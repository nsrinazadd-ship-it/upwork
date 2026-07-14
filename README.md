# Freelance Marketplace API (Work in Progress )

An elegant, highly secure, and robust RESTful API for a Freelance Marketplace, built with **Laravel** and **MySQL**. This project is designed following advanced software engineering patterns, prioritizing clean code, security, and scalability.

> **Status:** This project is currently under active development. Features are being added incrementally following strict SOLID principles.

---

##  Software Engineering & Best Practices Applied

Unlike typical basic CRUD applications, this API is architected with a production-ready mindset:
*   **Single Responsibility Principle (SRP):** Controllers are kept strictly "thin" (Skinny Controllers). Data validation is fully delegated to **Form Requests**, and authorization to **Policies**.
*   **Database Safety & Integrity:** Critical multi-row operations (such as accepting a proposal and updating project status) are wrapped in **DB Transactions** to prevent data corruption.
*   **API Security:** Secured via **Laravel Sanctum** token-based authentication with protection against ID spoofing and BOLA (Broken Object Level Authorization).
*   **Standardized Responses:** Implements a custom unified `ApiResponse` trait to ensure consistent JSON structures for all success and error responses.

---

##  Tech Stack

*   **Backend Framework:** Laravel (PHP)
*   **Database:** MySQL 
*   **Authentication:** Laravel Sanctum (Token-Based)
*   **Architecture Pattern:** RESTful API Design

---

##  Project Roadmap & Features

### 1. Authentication & Profile Management
- [x] Secure registration with Role-based assignment (`Client` / `Freelancer`)
- [x] Login & Token generation with Device Name binding
- [x] Secure logout (Revoking only the current device's access token)
- [x] Comprehensive Profile view & secure updates with custom Mutators/Accessors for avatars and links

### 2. Project Management
- [x] Public listing (with pagination) and detailed single project view
- [x] Create project (restricted to `Client` role)
- [x] Secure project editing & deletion (guarded via Laravel Policies to ensure only the owner can modify)
- [x] Project-Skills association via a robust pivot table system

### 3. Proposals Ecosystem (Core Business Logic)
- [x] Submit proposals (restricted to `Freelancer` role, prevents duplicate proposals)
- [x] Client action: Accept a proposal (automatically updates project status to `in_progress` and rejects other pending proposals)
- [x] Client action: Reject a proposal

### 4. Reviews & Ratings
- [x] Post-project reviews (automatically detects the roles and assigns the review to the correct party)
- [x] Rating system (preventing duplicate reviews on the same project)

### 5. Upcoming Features (Under Development )
- [ ] Notifications System (bell notifications for proposal status changes)
- [ ] User Dashboard / Analytics endpoint
- [ ] Project categorization & advanced search filters
- [ ] Integrate caching mechanisms (using Redis or Database Driver) to store frequently requested skills and highly popular projects.
- [ ] Implement API rate limiting and throttling (e.g., restricting failed login attempts) to safeguard endpoints against brute-force and       DDoS/flood attacks.

---

##  API Endpoints (Brief Overview)

### Auth & Profile
*   `POST /api/register` - Create a new account
*   `POST /api/login` - Authenticate and get a bearer token
*   `POST /api/logout` - Revoke token (Auth Required)
*   `GET /api/profile` - Fetch current user's profile (Auth Required)
*   `PUT /api/profile` - Update profile details (Auth Required)

### Projects
*   `GET /api/projects` - List all projects
*   `GET /api/projects/{id}` - Fetch single project details
*   `POST /api/projects` - Create a new project (Auth & Client Only)
*   `PUT /api/projects/{id}` - Edit a project (Auth & Owner Only)
*   `DELETE /api/projects/{id}` - Delete/Close a project (Auth & Owner Only)

### Proposals & Reviews
*   `POST /api/proposals` - Submit a proposal (Freelancer Only)
*   `PATCH /api/proposals/{id}/accept` - Accept a proposal (Client Owner Only)
*   `PATCH /api/proposals/{id}/reject` - Reject a proposal (Client Owner Only)
*   `POST /api/reviews` - Rate and review a completed project

---
