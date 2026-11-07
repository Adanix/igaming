# NexusPlay — Turnkey iGaming Platform, Seamless USD Wallet & Backoffice Demo

NexusPlay is an enterprise-grade turnkey iGaming platform demonstration engineered with a high-throughput Seamless USD Wallet, modular Game Aggregator Simulator, Sandbox Payment Gateway with HMAC-SHA256 signature verification, Multi-Tier Affiliate System with Net Gaming Revenue (NGR) revshare calculation, and a comprehensive Backoffice Operations & Forensic Compliance Suite.

Built on modern Laravel, Tailwind CSS, Alpine.js, and Chart.js, NexusPlay illustrates the full end-to-end lifecycle of an online gaming operator—from player registration, deposit orchestration, and real-time spin resolution, to risk-based withdrawal gating, bonus rollover progression, affiliate tracking, and mathematical reconciliation.

---

## 🏛️ System Architecture & Financial Ledger

### 1. Atomic Double-Entry Ledger (`WalletLedgerService`)
Financial accuracy is the bedrock of the platform. NexusPlay does not rely on mutable balance increments. Instead, all monetary transactions execute through a strictly balanced, double-entry ledger.

- **Base Currency**: Stored globally as USD (`DECIMAL(18, 2)`).
- **Three-Bucket Balance Model**:
  - `cash_balance_usd`: Fully withdrawable player funds deposited or converted from bonuses.
  - `bonus_balance_usd`: Promotional balances bound to rollover wagering requirements.
  - `locked_withdrawal_usd`: Cash reserved during pending withdrawal processing to eliminate double-spend risks.
- **Transactional Atomicity**: Wagers, winnings, refunds, and adjustments run inside database transactions with pessimistic row locking (`lockForUpdate`).
- **Idempotency Protection**: Every ledger entry requires an idempotency key (e.g. `idemp-bet-{uuid}`, `idemp-dep-{invoice}`, `idemp-vip-cb-{wallet}-{year-week}`). Duplicate payloads are rejected immediately with zero balance mutation.
- **Automated Rollback Engine**: If a simulated game round fails or cancels mid-flight, an inverse `game_rollback_credit` transaction restores player balances in real time.
- **Zero-Discrepancy Mathematical Reconciliation**: The platform provides an automated audit engine comparing aggregated ledger rows against snapshot wallet balances:
  $$\text{Discrepancy} = \sum(\text{Ledger Entries}) - \sum(\text{Wallet Balances}) = \$0.00$$

---

## 🎮 Core Functional Modules

### 1. Player Experience & Casino Lobby
- **Interactive Game Catalog**: Categorized by Slots, Live Casino, Crash / Instant Win, and Table Games.
- **Real-Time Search & Filtering**: Instant client-side search by title or provider (Pragmatic Play, Evolution, Spribe, PG Soft, Hacksaw Gaming).
- **Personal Favorites**: Players can toggle game favorites with persistent database state.
- **Game Stage Viewport**: Responsive HTML5 / Canvas game simulator supporting dynamic spin mechanics and real-time balance sync.
- **Deterministic Outcome Selector (Testing Mode)**: Evaluators can force game outcomes directly on the stage:
  - *Random*: Natural statistical RTP distribution (96.5% standard).
  - *Guaranteed Win (2x)*: Deterministic double payout.
  - *Guaranteed Big Win (25x)*: High-multiplier payout for jackpot testing.
  - *Near Miss / Loss*: Edge-case visual teaser with zero payout.
- **Round Audit Drawer**: Detailed drawer displaying Round Code, Bet Amount, Outcome Multiplier, Net Payout, Post-Round Balances, and Cryptographic Outcome Hash.

### 2. Cashier, Sandbox Gateway & Withdrawal Workflow
- **Multi-Rail Deposit Sandbox**:
  - Credit / Debit Card (Visa / Mastercard)
  - Cryptocurrency (USDT TRC-20)
  - Local E-Wallets / QRIS
  - Direct Instant Bank Wire
- **HMAC-SHA256 Webhook Callbacks**: Simulates production asynchronous payment notifications. Payloads are signed with secret keys, verified for timestamp skew, and safeguarded against replay attacks.
- **Risk-Gated Withdrawal System**:
  - Balance validation ensures requested cash does not exceed available unreserved funds.
  - Funds are moved immediately from `cash_balance_usd` to `locked_withdrawal_usd`.
  - Anti-drain rules ensure active rollover obligations are completed prior to cashout.
  - Automated High-Value Escalation: Withdrawals exceeding $1,000.00 are automatically tagged as `is_high_value = true`, requiring manual review and approval by Operations/Finance staff.
  - Player Cancel Action: Players can voluntarily cancel pending withdrawals, immediately restoring locked funds back to available cash.

### 3. Bonus Engine & Rollover Tracker
- **Promotional Campaigns**: First Deposit Match (100% up to $500.00, 15x rollover), VIP Reload Boost, and Free Play chips.
- **Wagering Accumulator**: Every wager placed using cash or bonus funds automatically increments the bonus rollover accumulator.
- **Automated Cash Conversion**: When wagering requirements are met, bonus balances are automatically swept into withdrawable cash via ledger conversion entries.

### 4. VIP Loyalty Club & Cashback Engine
- **5 Progressive VIP Tiers**: Bronze, Silver, Gold, Platinum, and Diamond.
- **Dynamic Tier Progression**: Evaluates player lifetime turnover against tier thresholds.
- **Weekly Loss Cashback**: Calculates settled net losses and awards percentage-based cashback directly into the player's cash balance.
- **Weekly Idempotency Lock**: Protects operators from duplicate cashback claims within the same calendar week.

### 5. Multi-Tier Affiliate Partner Portal
- **Performance KPIs**: Total Referral Clicks, Registered Players, First Time Deposits (FTD), Total Turnover, Gross Gaming Revenue (GGR), and Net Gaming Revenue (NGR).
- **Industry-Standard NGR Formula**:
  $$\text{NGR} = (\text{GGR} - \text{Bonus Expensed}) \times 0.85$$
- **Tiered RevShare**: 30% baseline commission automatically accrued from player activity.
- **Dynamic Referral Tracking**: Generates unique referral parameters for player acquisition.
- **Commission Settlement & Payout**: Affiliates can review itemized referral performance and request commission disbursements.

### 6. Backoffice, Security & Operations Console
- **Simulated 2FA / TOTP Challenge Gate**: Secures administrative access; all backoffice routes enforce simulated MFA verification.
- **Executive KPI Dashboard**: Real-time operational figures, player acquisition trends, turnover velocity, and live reconciliation health widget.
- **Player Directory & Risk Profiling**: Full player search, KYC level progression, self-exclusion toggles, and manual balance adjustment with required audit justification.
- **Catalog & Limits Management**: Real-time activation, maintenance mode toggles, and min/max bet adjustments per game.
- **Payment & Withdrawal Review**: Queue management for high-value cashout approvals or rejections with automated audit logging.
- **Cryptographic Audit Trail**: Chained SHA-256 hash log guaranteeing non-repudiation and forensic data integrity across administrative actions.
- **Webhook Telemetry**: Complete inspectable log of inbound payment gateway webhooks, headers, raw payloads, and signature verification outcomes.

---

## 👥 Pre-Configured Demo Personas

The platform includes five production-ready demo personas with zero-password authentication for frictionless evaluation:

| Persona | Role | Identifier | Opening State | Test Scenarios |
| :--- | :--- | :--- | :--- | :--- |
| **Alex Mercer** | Standard Player | `alex.mercer@demo.nexusplay.com` | Cash: $2,450.00<br>Bonus: $150.00<br>Tier: Bronze VIP | Test slot and crash game spins, sandbox deposits, bonus rollover tracking, and weekly cashback claim. |
| **Victoria Sterling** | High Roller VIP | `victoria.sterling@demo.nexusplay.com` | Cash: $12,850.00<br>Locked: $1,500.00<br>Tier: Platinum VIP | Test high-value withdrawal states, pending withdrawal cancellation, and priority tier benefits. |
| **Marcus Vance** | Affiliate Partner | `marcus.vance@demo.nexusplay.com` | Unpaid Commission: $1,420.00<br>RevShare: 30% | Inspect referral click attribution, player turnover, NGR calculations, and submit payout requests. |
| **Nadia Kusuma** | Operations & Finance | `nadia.kusuma@demo.nexusplay.com` | Ops & Compliance Access | Review and approve Victoria's $1,500.00 withdrawal, inspect webhook logs, and verify player KYC levels. |
| **Damian Thorne** | Super Admin | `damian.thorne@demo.nexusplay.com` | Executive & Security Access | Complete TOTP challenge, review zero-discrepancy reconciliation, perform manual balance adjustments, and audit SHA-256 hash chains. |

---

## 🛠️ Technology Stack & Engineering Standards

- **Backend Framework**: Laravel 13 / PHP 8.5
- **Database Engine**: MySQL 8.4 (InnoDB, Strict Mode, Foreign Key Constraints, Decimal Math)
- **Frontend Layer**: Blade Templates, Tailwind CSS v4, Alpine.js v3, Chart.js
- **Localization**: Dual-language support (English and Indonesian) with session persistence
- **Security Protocols**: HMAC-SHA256 webhooks, SHA-256 chained audit logs, CSRF protection, strict role-based access control (RBAC), and session timeouts
- **Code Quality & Testing**: 100% test pass rate across 34 automated feature and unit tests with 137 assertions

---

## 🧪 Automated Testing Suite

The test suite covers public navigation, authentication, seamless wallet transactions, game simulation, webhook processing, withdrawal risk checks, and administrative security:

```bash
php artisan test
```

### Test Coverage Highlights:
- `P0PublicAndDemoAuthTest`: Public home rendering, bilingual switching, documentation sections, demo persona logins, and database reset.
- `SeamlessWalletAndGameSimulatorTest`: Lobby filters, game stage launch, idempotent ledger entries on spin, big win multipliers, atomic rollback on error, rollover incrementing, VIP status rendering, and weekly cashback idempotency.
- `PaymentGatewayAndWithdrawalWorkflowTest`: Deposit sandbox invoice creation, valid HMAC-SHA256 signature crediting, invalid signature rejection, anti-replay attack blocking, standard withdrawal cash locking, withdrawal cancellation balance restoration, and high-value flag assignment.
- `BackofficeSecurityAndAffiliateTest`: Admin MFA TOTP challenge, zero-discrepancy financial reconciliation, manual balance adjustment with audit logging, game status and limit toggles, affiliate NGR calculation, commission payout processing, and SHA-256 hash chain verification.

---

## 🚀 Local Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- Node.js 20 or higher & npm
- MySQL 8.0 or higher (or MariaDB 10.11+)

### Installation Steps

1. **Clone the Repository**:
   ```bash
   git clone git@github.com:Adanix/igaming.git
   cd igaming
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies & Build Assets**:
   ```bash
   npm install
   npm run build
   ```

4. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database Connection**:
   Update your `.env` configuration with your MySQL credentials:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=igaming_db
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   ```

6. **Run Migrations & Seed Demo Personas**:
   ```bash
   php artisan migrate --seed
   ```

7. **Start the Application**:
   ```bash
   php artisan serve
   ```

---

## 📄 License

This software is developed for demonstration, turnkey evaluation, and technical portfolio benchmarking. All rights reserved.
