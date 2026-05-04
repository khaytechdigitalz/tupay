TUPAY PROJECT README
DEVELOPED AND DEPLOYED BY OLUWAKAYODE ADETUNJI

├── app
│   ├── Helpers      # Utility functions (e.g., User-Agent parsing)
│   ├── Http
│   │   ├── Controllers  # Request handling logic
│   │   ├── Middleware   # Security layers (Signature verification)
│   │   └── Kernel.php
│   ├── Jobs         # Asynchronous tasks (Settlement processing)
│   ├── Models       # Eloquent models & casting logic
│   ├── Providers    # Service bootstrapping
│   └── Services     # Core business logic (Exchange calculations)
├── bootstrap
├── config           # System & Service configurations
├── database
│   ├── factories
│   ├── migrations   # Schema definitions (wallets, transactions, jobs)
│   └── seeders
├── public           # Entry point (index.php)
├── resources        # UI assets (if applicable)
├── routes           # API endpoint definitions
├── storage
└── vendor

2. Concurrency Strategy
To prevent Double-Spending and Race Conditions, TuPay employs a multi-layered locking strategy:

Application-Level (Pessimistic Locking): We implement Redis Atomic Locks using a unique key per user (e.g., swap_lock_user_{id}). This prevents a user from triggering multiple concurrent requests before the first one completes.

Database-Level Locking: Inside the transaction, we use lockForUpdate() on wallet rows. To prevent Deadlocks, wallet IDs are always sorted before acquisition.

Strict Idempotency: Every critical operation requires an idempotency_key (UUID). The system checks this key in the transaction metadata to ensure a retry never executes the logic twice.

3. Security Measures
Security is baked into the transaction lifecycle:

Two-Factor Authentication (2FA): High-value actions like confirmSwap require a 6-digit OTP. The OTP is hashed (bcrypt) and invalidated immediately upon a successful swap.

Signature Verification: Inbound webhooks from settlement partners require an X-TuPay-Signature. We utilize a Mock Shared Secret and HMAC-SHA256 hashing. Verification is performed using hash_equals to mitigate timing attacks.

4. Performance Optimization
To maintain high throughput, we utilize Redis:

Rate Caching: Exchange rates are fetched and cached in Redis with a short TTL (e.g., 60 seconds) to avoid redundant DB queries.

Asynchronous Processing: Third-party settlement confirmations are handled via Queued Jobs (stored in the jobs table). This decouples the partner's HTTP response from our internal ledger updates.

5. Assumptions
Subunit Precision: All financial calculations are performed in subunits (e.g., Kobo/Fen) using integers to avoid floating-point errors.

Mock Partner: We assume the third-party settlement partner provides a unique reference for every payout for idempotency.

Audit Trail: Capturing the IP, Browser, and Device ID is assumed sufficient for initial compliance.