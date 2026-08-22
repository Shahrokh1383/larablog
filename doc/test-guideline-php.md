# Testing Architecture Guide — Larablog
**Single Source of Truth (SSOT) — Behavioral Testing & Quality Assurance**

> **Core Philosophy**  
> Tests are **Executable Specifications** of the Bounded Contexts.  
> We test **Behavior and State Transitions**, never implementation details.  
> **Pest PHP** is the absolute standard. Real databases for integration, pure isolation for contracts.  
> This guide empowers developers and AI to test *any* module infinitely without relying on rigid templates.

---

## 1. The Testing Pyramid & Directory Strategy

The test suite must perfectly mirror the `src/Modules/{Context}/` structure. All tests for a module are consolidated under a single feature directory: `tests/Feature/{Context}/`. This ensures that when a developer or AI navigates to a specific domain class, the corresponding behavioral specification is exactly one directory up in the `tests/` tree.

### 1.1. Architecture Tests (The Guards)
*   **Location:** `tests/Architecture/`
*   **Purpose:** Enforce the Open-World Modular Architecture. These tests do not execute business logic; they analyze the static structure of the codebase.
*   **Strategy:** Define strict dependency rules. Ensure no Bounded Context imports another Context's internal classes (Models, Controllers, Concrete Services). Allow only Shared Kernel imports and Cross-Module Service Contracts/Events.
*   **Execution:** Must run **first** in the CI pipeline. If boundaries are violated, the build fails immediately before any database tests run.

### 1.2. Feature Tests (The Black Box & Contract Specs)
*   **Location:** `tests/Feature/{Context}/`
*   **Purpose:** Validate the complete HTTP lifecycle, domain logic, and pure contracts from within one cohesive module test space.
*   **Strategy:** Organize tests into subdirectories that mirror the module layers:
    - `Http/Controllers/` – full HTTP lifecycle, authentication, response status, JSON structure, database state.
    - `Http/Requests/` – exact validation rules and authorization contracts.
    - `Http/Resources/` – exact output transformation shape.
    - `Policies/` – access control truth tables.
    - `Services/` – business rules and state transitions with real Eloquent and mocked cross-module dependencies.
    - `Actions/` (if needed) – reusable domain operations.
*   **Rule:** Never mock internal services or controllers in Controller tests. Use real Eloquent models and a real, refreshed database. For cross-module synchronous service calls, mock the public interface to isolate the Bounded Context.

---

## 2. Layer-by-Layer Behavioral Strategy

To test any new module or feature, apply the following mental models to each architectural layer:

### A. FormRequests (Validation & Authorization)
*   **What to test:** The exact contract of what the system accepts and who is allowed to ask for it.
*   **How:** Instantiate the Request class. Assert that the `authorize()` method returns the correct boolean based on the current user context. Assert that the `rules()` method returns the exact, expected array of validation constraints.

### B. Policies (Access Control)
*   **What to test:** The truth table of permissions.
*   **How:** Instantiate the Policy. Create various user states (e.g., Admin, Owner, Guest, Suspended) and target model states. Iterate through every ability method and assert the strict boolean outcome (`true`/`false`) for every permutation of user and target. Use Mockery for the `HasRolesContract` to avoid database overhead.

### C. API Resources (Output Transformation)
*   **What to test:** The exact shape of the data leaving the system.
*   **How:** Pass a fully hydrated, model instance into the Resource. Convert it to an array and assert the presence of exact keys, the correct data types, and the proper formatting of dates or nested relationships.

### D. Services & Actions (Business Rules)
*   **What to test:** Complex domain logic, state mutations, and transactional integrity.
*   **How:** Instantiate the Service or Action with real dependencies. For cross-module dependencies, mock the public interface to avoid coupling. Execute the method with specific DTOs. Assert that the database reflects the correct state transition and that Domain Events are dispatched when applicable.

### E. HTTP Controllers (End-to-End)
*   **What to test:** Complete request lifecycle, including authentication, authorization, validation, and response formatting.
*   **How:** Use `$this->getJson()`, `$this->postJson()`, etc. Provide an HTTP request and authentication state. Assert the HTTP status, JSON structure, and database state. Mock cross-module service interfaces to isolate the module.

---

## 3. The Mocking & Faking Doctrine

A strict boundary must be maintained between **Internal Domain Logic** and **External System Boundaries**.

| Component Category | Testing Strategy | Rationale |
| :--- | :--- | :--- |
| **Internal Domain (Models, Services, Actions)** | **Execute Real** | We must test the actual business logic and database mutations. Mocking internals leads to false positives. |
| **Database Layer** | **Real (Refreshed)** | Use `RefreshDatabase` or `DatabaseTransactions`. Never mock Eloquent queries or DB connections in Feature tests. |
| **Cross-Module Synchronous Calls** | **Mock / Fake** | Treat other Bounded Contexts as external boundaries. Mock the public Service Interface to prevent inter-module coupling and keep tests isolated. |
| **External APIs (OAuth, Payments, 3rd Party)** | **Mock / Fake** | Mock the HTTP client or SDK to prevent external calls and test edge cases. |
| **Cross-Module Events** | **Fake** | When testing Module A, fake the Event dispatcher to prevent Module B's listeners from executing. |
| **Time & Dates** | **Freeze** | Use time-traveling utilities to freeze the clock for deterministic results. |

---

## 4. Data Management Strategy

*   **Factories are Mandatory:** All test data must be generated using Model Factories. 
*   **No Seeders:** Never use database seeders in the test suite. Tests require precise, isolated, and predictable data states.
*   **State Definitions:** Utilize Factory States to create specific domain scenarios rather than manually overriding attributes in the test body.

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