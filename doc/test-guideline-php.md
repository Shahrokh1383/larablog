# Testing Architecture Guide — Larablog
**Single Source of Truth (SSOT) — Behavioral Testing & Quality Assurance**

> **Core Philosophy**  
> Tests are **Executable Specifications** of the Bounded Contexts.  
> We test **Behavior and State Transitions**, never implementation details.  
> **Pest PHP** is the absolute standard. Real databases for integration, pure isolation for units.  
> This guide empowers developers and AI to test *any* module infinitely without relying on rigid templates.

---

## 1. The Testing Pyramid & Directory Strategy

The test suite must perfectly mirror the `src/Modules/{Context}/` structure. This ensures that when a developer or AI navigates to a specific domain class, the corresponding behavioral specification is exactly one directory up in the `tests/` tree.

### 1.1. Architecture Tests (The Guards)
*   **Location:** `tests/Architecture/`
*   **Purpose:** Enforce the Open-World Modular Architecture. These tests do not execute business logic; they analyze the static structure of the codebase.
*   **Strategy:** Define strict dependency rules. Ensure no Bounded Context imports another Context's internal classes (Models, Controllers, Concrete Services). Allow only Shared Kernel imports and Cross-Module Service Contracts/Events.
*   **Execution:** Must run **first** in the CI pipeline. If boundaries are violated, the build fails immediately before any database tests run.

### 1.2. Feature Tests (The Black Box)
*   **Location:** `tests/Feature/{Context}/Http/Controllers/`
*   **Purpose:** Validate the complete HTTP lifecycle from the outside in.
*   **Strategy:** Treat the API as a black box. Provide an HTTP Request and an Authentication State. Assert the HTTP Response (Status, Headers, JSON Structure) and the resulting Database State.
*   **Rule:** Never mock internal services or controllers in Feature tests. Let the entire slice of the application execute against a real, refreshed database.

### 1.3. Integration Tests (The Business Rules)
*   **Location:** `tests/Feature/{Context}/Services/` & `Actions/`
*   **Purpose:** Validate complex domain logic, state mutations, and transactional integrity.
*   **Strategy:** Instantiate the Service or Action with real dependencies. Execute the method with specific DTOs. Assert that the database reflects the correct state transition and that the correct Domain Events are dispatched.
*   **Rule:** Assert the throwing of specific `DomainException`s when business rules are violated.

### 1.4. Unit Tests (The Pure Contracts)
*   **Location:** `tests/Unit/{Context}/` (DTOs, Events, Requests, Policies, Resources)
*   **Purpose:** Validate pure logic, data transformation, and access control without framework overhead.
*   **Strategy:** Instantiate classes directly. Pass pure inputs and assert pure outputs. These tests must be lightning-fast and require no database connection.

---

## 2. Layer-by-Layer Behavioral Strategy

To test any new module or feature, apply the following mental models to each architectural layer:

### A. FormRequests (Validation & Authorization)
*   **What to test:** The exact contract of what the system accepts and who is allowed to ask for it.
*   **How:** Instantiate the Request class. Assert that the `authorize()` method returns the correct boolean based on the current user context. Assert that the `rules()` method returns the exact, expected array of validation constraints.

### B. Policies (Access Control)
*   **What to test:** The truth table of permissions.
*   **How:** Instantiate the Policy. Create various user states (e.g., Admin, Owner, Guest, Suspended) and target model states. Iterate through every ability method and assert the strict boolean outcome (`true`/`false`) for every permutation of user and target.

### C. DTOs & Value Objects (Data Integrity)
*   **What to test:** Immutability and construction constraints.
*   **How:** Verify that readonly properties are correctly assigned upon instantiation. Ensure that invalid data types or missing required parameters fail at the construction level (if enforced by the language/framework).

### D. API Resources (Output Transformation)
*   **What to test:** The exact shape of the data leaving the system.
*   **How:** Pass a fully hydrated, factory-generated Model into the Resource. Convert it to an array and assert the presence of exact keys, the correct data types, and the proper formatting of dates or nested relationships.

### E. Events & Notifications (Side Effects)
*   **What to test:** Payload accuracy and dispatch triggers.
*   **How:** For Events, instantiate them and assert that their public properties hold the correct domain data. For Notifications/Mails, utilize the framework's Faking mechanism to intercept the dispatch, then assert that the notification was sent to the correct notifiable entity with the correct underlying data.

---

## 3. The Mocking & Faking Doctrine

A strict boundary must be maintained between **Internal Domain Logic** and **External System Boundaries**.

| Component Category | Testing Strategy | Rationale |
| :--- | :--- | :--- |
| **Internal Domain (Models, Services, Actions)** | **Execute Real** | We must test the actual business logic and database mutations. Mocking internals leads to false positives. |
| **Database Layer** | **Real (Refreshed)** | Use `RefreshDatabase` or `DatabaseTransactions`. Never mock Eloquent queries or DB connections in Feature/Integration tests. |
| **External APIs (OAuth, Payments, 3rd Party)**| **Mock / Fake** | These are system boundaries. Mock the HTTP client or SDK to prevent external calls, rate limits, and test edge cases (timeouts, 500 errors). |
| **Cross-Module Events** | **Fake** | When testing Module A, fake the Event dispatcher to prevent Module B's listeners from executing and polluting the test state. |
| **Time & Dates** | **Freeze** | Use time-traveling utilities to freeze the clock. This is critical for testing token expirations, cache TTLs, and scheduled events deterministically. |

---

## 4. Data Management Strategy

*   **Factories are Mandatory:** All test data must be generated using Model Factories. 
*   **No Seeders:** Never use database seeders in the test suite. Seeders are for development environments; tests require precise, isolated, and predictable data states.
*   **State Definitions:** Utilize Factory States to create specific domain scenarios (e.g., `User::factory()->suspended()->create()`) rather than manually overriding attributes in the test body.

---

## 5. Frontend Testing Strategy (Brief Alignment)

While the backend relies on Pest, the frontend (Next.js/React) must align with the same behavioral philosophy:

*   **Pages (App Router):** Tested via E2E (Playwright) to verify routing, auth gates, and overall composition.
*   **Hooks (Logic):** Tested via React Testing Library. Mock the `httpClient` and assert state transitions, loading states, and error handling.
*   **Components (Presentation):** Tested via React Testing Library. Pass props in, simulate DOM events out. Never test internal state or API calls inside a pure component.

---

## 6. The "Definition of Done" for Testing

Before any feature, module, or AI-generated code is considered complete, it must satisfy this behavioral checklist:

1.  [ ] **Boundary Check:** Are Architecture tests updated to ensure this new code does not leak across Bounded Contexts?
2.  [ ] **Happy Path:** Is the successful state transition tested end-to-end (HTTP -> DB -> JSON)?
3.  [ ] **Failure Modes:** Are all `DomainException` paths and validation failures tested with correct HTTP status codes (422, 403, 404)?
4.  [ ] **Access Control:** Are all Policy permutations (allow/deny) covered?
5.  [ ] **Contracts:** Are FormRequest rules and Resource structures strictly asserted?
6.  [ ] **Isolation:** Can this test run entirely alone, in any order, without relying on external state?

---
*This document defines the physics of testing in Larablog. By adhering to these behavioral models, developers and AI can confidently test any current or future Bounded Context with production-grade reliability.*