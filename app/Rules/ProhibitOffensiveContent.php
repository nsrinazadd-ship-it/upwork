<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Support\Facades\Log;

class ProhibitOffensiveContent implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(blank($value)){
            return;
        }

        try{
            $responce=Http::withToken(config('services.openai.key'))
            ->timeout(3)
            ->post('https://api.openai.com/v1/moderations',[
                'input'=>$value, ]);

                if($responce->successful()){
                    $isFlagged=$responce->json('results.0.flagged');

                    if($isFlagged){
                        $fail('المحتوى الذي أدخلته في ' . $attribute . ' يحتوي على ألفاظ غير لائقة أو مخالفة للسياسات.');
                    }
                }
        }catch(\Exception $e){
            Log::error('OpenAI Moderation API failed:',$e->getMessage());
            if($this->fallbackLocalCheck($value)){
                $fail('المحتوى يحتوي على كلمات محظورة (فحص احتياطي).');
            }
        }

    }
    protected function fallbackLocalCheck($value): bool
    {
        $badWords = ['كلمة_سيئة1', 'كلمة_سيئة2'];
        foreach ($badWords as $word) {
            if (str_contains(mb_strtolower($value), $word)) {
                return true;
            }
        }
        return false;
    }
}
