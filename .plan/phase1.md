# Counselling Practice — Conversion Plan

Source prototype: `.plan/index.html` (a single-file HTML/JS mockup of a counselling practice dashboard).
Target stack: Laravel 12 (PHP 8.4) + Filament v5, PHPUnit tests, Pint formatting.

The prototype calls a REST API (`/api/clients`, `/api/consents`, `/api/intake`, `/api/case-notes`,
`/api/resources`, `/api/logbook`, `/api/group-members`) and defines a sidebar with these modules:
Dashboard, Client Information, Individual Counselling, Group Counselling, Calendar,
Assessments & Tools, Workshops, Logbook, Reports, Resources.

We split the work into two phases: **Phase 1 = data layer (models + migrations + seeders)** and
**Phase 2 = Filament admin resources**.

---

## Phase 1 — Laravel Models & Data Layer

### Approach

1. Introduce one enum per fixed vocabulary (client type, client status, contact method,
   logbook category, resource category, appointment status, group status).
2. Generate migrations + models + factories + seeders together via
   `php artisan make:model -mfs --no-interaction`.
3. Keep the master **Client** record as the single source of truth (as stated in the demo banner:
   "One master client record shared by Individual Counselling and Group Counselling").
4. Wire Eloquent relationships that mirror the API shapes observed in the prototype
   (`c.notes[]`, `intake.saved`, `consent.signed`, `group_members`, `resources` etc.).
5. Add a `DemoSeeder` that produces "Client A / C001" so the Demo banner and Quick-actions
   in the HTML work out-of-the-box.
6. Write feature tests per model covering create/update/delete and the key relationships.
7. Run `vendor/bin/pint --dirty --format agent` after each batch of changes and
   `php artisan test --compact` before closing the phase.

### Enums (`app/Enums/`)

| Enum | Values (TitleCase keys) |
|---|---|
| `ClientType` | Individual, Group, Both |
| `ClientStatus` | Active, Closed, OnHold |
| `ContactMethod` | Phone, Whatsapp, Email, Other |
| `LogbookCategory` | Counselling, Group, Workshop, Other |
| `ResourceCategory` | AssessmentWebsites, AssessmentForms, Psychoeducation, CounsellingReferences, Worksheets, Other |
| `AppointmentStatus` | Scheduled, Confirmed, Completed, Cancelled, NoShow |
| `GroupStatus` | Planned, Active, Completed |
| `AssessmentStatus` | Active, Planned, ComingLater |

### Models & migrations

Each row = one model with a `-mfs` migration/factory/seeder. Foreign keys use
`foreignId(...)->constrained()->cascadeOnDelete()` unless noted.

| Model | Key columns | Relationships |
|---|---|---|
| `Client` | `client_code` (unique, e.g. `C001`), `display_name`, `real_name`, `client_type` (enum), `status` (enum), `dob`, `phone`, `email`, `occupation`, `preferred_contact` (enum), `emergency_contact`, `emergency_phone` | hasOne `intake`; hasMany `consents`, `caseNotes`, `appointments`, `assessmentResults`, `sessionRecordings`; belongsToMany `groups` via `group_members` |
| `Intake` | `client_id` (unique), sections 1-7 from the intake form: `full_name`, `dob`, `phone`, `email`, `occupation`, `preferred_contact`, `emergency_contact_name`, `emergency_relationship`, `emergency_phone`, `presenting_concern`, `onset`, `family_background`, `education_work`, `other_background`, `goals`, `strengths`, `risk_notes`, `counsellor_notes`, `saved_at` | belongsTo `client` |
| `Consent` | `client_id`, `full_name`, `signature_path` (stored PNG from `signature_data`), `signed_at`, `pdf_path` | belongsTo `client` |
| `CaseNote` | `client_id`, `session_date`, `summary`, `full_note` | belongsTo `client` |
| `MentalStateExam` | `client_id`, `session_date`, JSON `data`, `notes` | belongsTo `client` |
| `Group` | `code` (e.g. `Group 01`), `name`, `description`, `total_sessions`, `status` (enum), `next_session_date` | hasMany `groupSessions`, `groupMembers`; belongsToMany `clients` |
| `GroupMember` (pivot model) | `group_id`, `client_id`, `pseudonym` (e.g. `Aisha`, `G001`), `joined_at` | belongsTo `group`, `client`; hasMany `attendances` |
| `GroupSession` | `group_id`, `session_number`, `session_date`, `notes` | belongsTo `group`; hasMany `attendances` |
| `GroupAttendance` | `group_session_id`, `group_member_id`, `attended` (bool), `notes` | belongsTo `groupSession`, `groupMember` |
| `Appointment` | `client_id` (nullable), `group_id` (nullable), `scheduled_at`, `duration_minutes`, `type` (individual/group), `status` (enum), `notes` | belongsTo `client`, `group` |
| `SessionRecording` | `client_id`, `appointment_id` (nullable), `label`, `file_path`, `duration_seconds`, `recorded_at` | belongsTo `client`, `appointment` |
| `Assessment` | `name`, `description`, `type` (form/interactive), `status` (enum), `scoring` (bool) | hasMany `assessmentResults` |
| `AssessmentResult` | `client_id`, `assessment_id`, JSON `answers`, JSON `scores`, `interpretation`, `taken_at` | belongsTo `client`, `assessment` |
| `Workshop` | `name`, `description`, `total_sessions` | hasMany `workshopMaterials` |
| `WorkshopMaterial` | `workshop_id`, `name`, `type`, `file_path` | belongsTo `workshop` |
| `LogbookEntry` | `user_id`, `week_label`, `entry_date`, `category` (enum), `activity`, `from_time`, `to_time`, `hours` (decimal 5,2), `client_code`, `notes` | belongsTo `user` |
| `Resource` | `user_id` (owner), `name`, `category` (enum), `url`, `description`, `file_name`, `file_path` | belongsTo `user` |

Notes / constraints:
- `client_code` auto-generated (`C001`, `C002`…) via a `booted()` hook on `Client`.
- `GroupMember.pseudonym` is used inside groups instead of `real_name` (privacy — matches the
  `🔒 Private` cells in the participants table).
- `LogbookEntry.hours` computed on save from `from_time`/`to_time` to match `calculateLogHours()`.
- Store `Consent.signature_data` on the `public` disk with `->visibility('public')` only if
  the counsellor explicitly needs a shareable link; default to `private`.

### Seeders

- `DemoSeeder`: creates the counsellor `User`, one `Client` (`C001` / "Client A" / "Career
  uncertainty"), one signed `Consent`, one completed `Intake`, three `CaseNote`s, one
  `Group` ("Building Healthy Habits in a Digital World", 8 members: Aisha, Ben, C, …),
  two `GroupSession`s with attendance, seed `Assessment`s (RIASEC active, Genogram planned),
  one `Workshop` ("Building Healthy Digital Habits") with three materials, and a handful of
  `LogbookEntry`s and starter `Resource`s.
- Register in `DatabaseSeeder`.

### Tests (`tests/Feature/Models/`)

One PHPUnit feature test per model asserting: factory creates a valid record, required
relationships work, and enum casts round-trip. Add a `ClientCodeGenerationTest` for the
`C001` sequence and a `LogbookHoursTest` for the hours calculation.

### Phase 1 exit criteria

- `php artisan migrate:fresh --seed` runs clean.
- `php artisan test --compact` green.
- `vendor/bin/pint --dirty --format agent` clean.

---

## Phase 2 — Filament Resources

### Approach

1. Scaffold each resource with `php artisan make:filament-resource {Model} --generate --no-interaction`.
2. Group resources in the sidebar to mirror the prototype's navigation:
   *Clients*, *Counselling*, *Groups*, *Practice*, *Library*.
3. Reuse enums directly in `Select::make(...)->options(EnumClass::class)`.
4. Follow the Filament rules bundled in the project guidelines:
   `Filament\Forms\Components\*`, `Filament\Schemas\Components\*`, `Filament\Tables\Columns\*`,
   `Filament\Actions\*`. Use `Repeater::make()->relationship()->schema([...])` for HasMany
   inline editing. Use `Get`/`Set` for reactive fields.
5. Add a Dashboard with widgets mirroring the four stat cards
   (Active individual clients, Active groups, Notes to complete, Practicum hours) and
   "Today's appointments" + "Quick actions".
6. Write Filament Livewire tests using `pestphp/pest-plugin-livewire`-style calls but as
   PHPUnit (per project rule: convert Pest → PHPUnit).

### Resources & key form/table hints

| Resource | Nav group | Form highlights | Table highlights |
|---|---|---|---|
| `ClientResource` | Clients | Section 1 personal info; Section 2 emergency contact; `Select` for `client_type`, `status`, `preferred_contact`; `Repeater` for case notes; relation managers for Intake, Consent, CaseNotes, Assessments, Recordings | columns: `client_code`, `display_name`, `real_name` (toggleable, restricted), contact, `status` badge; `SelectFilter` on status/type; searchable |
| `IntakeResource` (via RelationManager on Client) | Clients | 7 sections built with `Section::make()` + `Grid::make(2)`; long textareas; auto-fills from client on create | not listed on sidebar |
| `ConsentResource` (RelationManager) | Clients | full_name, signature preview (`ImageEntry` in infolist), `signed_at`; action "Download PDF" | list with `signed_at`, badge if signed |
| `CaseNoteResource` (RelationManager) | Counselling | `session_date`, `summary`, `full_note` textarea | most-recent first |
| `AppointmentResource` | Counselling | client OR group (reactive `->live()` type switch using `Get/Set`), `scheduled_at`, duration, status | calendar-style table; `SelectFilter` on status/type |
| `GroupResource` | Groups | name, code, total_sessions, status; Repeater for group_members with pseudonym + client select; RelationManagers for `GroupSession`s and attendance | code, name, member count (computed via `->state()`), status badge |
| `SessionRecordingResource` (RelationManager on Client / Appointment) | Counselling | file upload (`FileUpload`), duration, recorded_at; `->visibility('private')` | audio filename, duration, download action |
| `AssessmentResource` | Practice | name, description, type, status, scoring | status badge; filter by status |
| `AssessmentResultResource` (RelationManager on Client) | Practice | assessment select, JSON `answers` via `KeyValue`, `interpretation` textarea | assessment name, taken_at |
| `WorkshopResource` | Practice | name, description, total_sessions; Repeater for materials with FileUpload | list of workshops with material count |
| `LogbookEntryResource` | Practice | week, date, category select, activity, from/to time (reactive → hours via `Set`), client_code, notes | totals summariser widget for hours by category |
| `ResourceResource` | Library | name, category, url, description, file upload | filter by category, search |

### Dashboard (Filament `Page` + Widgets)

- `PracticeStatsOverview` (StatsOverviewWidget): Active individual clients, Active groups,
  Notes to complete, Practicum hours.
- `TodaysAppointmentsWidget` (TableWidget): today's `Appointment`s.
- `QuickActionsWidget` (custom view): buttons for New client, New appointment,
  New assessment, New workshop, Open demo client consent.

### Tests (`tests/Feature/Filament/`)

- `ClientResourceTest`: list search, create, edit (no redirect assertion for edit page),
  validation, delete.
- `GroupResourceTest`: repeater save for members, attendance table action.
- `LogbookEntryResourceTest`: from/to → hours reactive update, category filter.
- `ResourceResourceTest`: file upload path stored, category filter.
- `DashboardTest`: widgets render, stats match seeded data.

### Phase 2 exit criteria

- `/admin` fully navigable, all sidebar items map to a working resource or dashboard tile.
- All resource tests green (`php artisan test --compact`).
- Pint clean; `php artisan filament:upgrade` produces no drift.

---

## Notes / open questions for the user

- **Auth model**: assume a single-counsellor practice (existing `User`). Multi-tenant later?
- **Signatures**: prototype base64-encodes the canvas. We'll store as a PNG on disk and
  keep a `signature_path` column. Confirm PDF generation library (e.g. `barryvdh/laravel-dompdf`).
- **Calendar page**: prototype shows an empty placeholder. Phase 2 renders a table view;
  a full calendar (e.g. `saade/filament-fullcalendar`) is out of scope unless requested.
- **Reports page**: prototype shows four cards without implementation — treat as Phase 3.
