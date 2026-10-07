<?php

namespace Tests\Unit;

use App\Rules\ProhibitOffensiveContent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProhibitOffensiveContentTest extends TestCase
{
    private function errorFor(string $value): ?string
    {
        $error = null;

        (new ProhibitOffensiveContent)->validate('title', $value, function (string $message) use (&$error) {
            $error = $message;
        });

        return $error;
    }

    public function test_flagged_content_fails_validation(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['results' => [['flagged' => true]]])]);

        $this->assertNotNull($this->errorFor('some text'));
    }

    public function test_clean_content_passes(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['results' => [['flagged' => false]]])]);

        $this->assertNull($this->errorFor('some text'));
    }

    public function test_when_the_api_is_down_clean_content_passes(): void
    {
        Http::fake(['api.openai.com/*' => fn () => throw new ConnectionException('timeout')]);

        $this->assertNull($this->errorFor('some text'));
    }

    public function test_when_the_api_is_down_the_local_fallback_still_blocks_bad_words(): void
    {
        Http::fake(['api.openai.com/*' => fn () => throw new ConnectionException('timeout')]);

        $this->assertNotNull($this->errorFor('عنوان فيه كلمة_سيئة1'));
    }
}
