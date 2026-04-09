-- ============================================================
-- ER DRILL FORM - DATABASE SCHEMA
-- Version  : 1.0
-- Date     : 2026-04-09
-- Description:
--   Full schema for the Emergency Response Drill Management
--   System. Covers authentication, role rotation tracking,
--   drill records, events, actions, and attachments.
-- ============================================================


-- ============================================================
-- 1. ER_DRILL_USER_LIST
--    Central user/role account table. Each row represents a
--    shared role account per rig (e.g. STO - Rig Alpha).
--    full_name reflects whoever currently holds the rotation.
-- ============================================================
CREATE TABLE er_drill_user_list (
    id                      INT             PRIMARY KEY IDENTITY(1,1),

    -- Identity
    full_name               VARCHAR(255)    NOT NULL,
    email                   VARCHAR(255)    NOT NULL UNIQUE,
    role                    VARCHAR(100)    NOT NULL,           -- 'STO', 'BE', 'OIM', 'Admin', etc.
    rig                     VARCHAR(100)    NULL,
    active_status           BIT             NOT NULL DEFAULT 1,
    description             NVARCHAR(500)   NULL,

    -- Authentication
    password_hash           VARCHAR(255)    NOT NULL,           -- bcrypt hash; never store plain text
    last_login              DATETIME2       NULL,
    failed_login_attempts   INT             NOT NULL DEFAULT 0,
    locked_until            DATETIME2       NULL,               -- Temporary lockout after failed attempts
    must_change_password    BIT             NOT NULL DEFAULT 1, -- Force reset on first login

    -- Password Reset
    reset_token             VARCHAR(255)    NULL,               -- One-time reset token (stored hashed)
    reset_token_expires     DATETIME2       NULL,

    -- Session / Token
    refresh_token_hash      VARCHAR(255)    NULL,               -- Hashed JWT refresh token
    refresh_token_expires   DATETIME2       NULL,

    -- Name Confirmation (rotation handover gate)
    -- NULL or stale value triggers "Is this still you?" prompt on login
    name_confirmed_at       DATETIME2       NULL,

    -- Audit
    created_at              DATETIME2       NOT NULL DEFAULT GETUTCDATE(),
    updated_at              DATETIME2       NOT NULL DEFAULT GETUTCDATE()
);


-- ============================================================
-- 2. ER_DRILL_ROLE_HISTORY
--    Tracks who physically held each shared role account over
--    time. One row per person per rotation period.
--    effective_to = NULL means currently active.
-- ============================================================
CREATE TABLE er_drill_role_history (
    id              INT             PRIMARY KEY IDENTITY(1,1),
    user_id         INT             NOT NULL,               -- FK to er_drill_user_list (the role account)
    person_name     VARCHAR(255)    NOT NULL,               -- The individual (e.g. Josh, Kane)
    rig             VARCHAR(100)    NOT NULL,
    role            VARCHAR(100)    NOT NULL,               -- Redundant copy for easy querying
    effective_from  DATE            NOT NULL,
    effective_to    DATE            NULL,                   -- NULL = currently active rotation
    remarks         NVARCHAR(500)   NULL,                   -- e.g. 'Rotation handover Apr 2025'
    created_at      DATETIME2       NOT NULL DEFAULT GETUTCDATE(),

    CONSTRAINT fk_rolehistory_user
        FOREIGN KEY (user_id)
        REFERENCES er_drill_user_list(id)
        ON DELETE CASCADE
);


-- ============================================================
-- 3. ER_DRILL_RECORDS
--    Parent/central table. Every drill produces one record.
--    FK columns link to role accounts; name columns are
--    point-in-time snapshots auto-filled at record creation
--    and locked on submission to preserve the audit trail.
-- ============================================================
CREATE TABLE er_drill_records (
    id                          INT             PRIMARY KEY IDENTITY(1,1),

    -- Drill Details
    drill_type                  VARCHAR(100)    NOT NULL,
    event_type                  VARCHAR(100)    NOT NULL,
    on_duty_crews               NVARCHAR(500)   NULL,
    event_location              VARCHAR(255)    NULL,
    scenario                    NVARCHAR(MAX)   NULL,
    applicable_dsha             VARCHAR(255)    NULL,
    performance_standard        NVARCHAR(MAX)   NULL,
    performance_standards_met   VARCHAR(50)     NULL,       -- 'Yes', 'No', 'Partial'
    objectives                  NVARCHAR(MAX)   NULL,
    debrief_attendees           NVARCHAR(MAX)   NULL,
    positive_observations       NVARCHAR(MAX)   NULL,
    improvement_opportunities   NVARCHAR(MAX)   NULL,
    other_comments              NVARCHAR(MAX)   NULL,

    -- Role Account FK (links to the shared role account)
    sto_user_id                 INT             NULL,
    be_user_id                  INT             NULL,
    oim_user_id                 INT             NULL,

    -- Name Snapshot (who physically held the role at time of drill)
    -- Auto-populated from er_drill_user_list.full_name at record creation.
    -- Locked after submission. Preserved even after rotation changes.
    sto_name                    VARCHAR(255)    NULL,
    be_name                     VARCHAR(255)    NULL,
    oim_name                    VARCHAR(255)    NULL,

    -- Workflow
    status                      VARCHAR(50)     NOT NULL DEFAULT 'Draft',
                                                            -- 'Draft', 'Submitted', 'Approved', 'Closed'
    -- Audit
    created_at                  DATETIME2       NOT NULL DEFAULT GETUTCDATE(),
    updated_at                  DATETIME2       NOT NULL DEFAULT GETUTCDATE(),

    -- ON DELETE SET NULL: if a user account is removed, FK becomes NULL
    -- but name snapshot is still preserved in the _name columns.
    CONSTRAINT fk_records_sto FOREIGN KEY (sto_user_id) REFERENCES er_drill_user_list(id) ON DELETE SET NULL,
    CONSTRAINT fk_records_be  FOREIGN KEY (be_user_id)  REFERENCES er_drill_user_list(id) ON DELETE SET NULL,
    CONSTRAINT fk_records_oim FOREIGN KEY (oim_user_id) REFERENCES er_drill_user_list(id) ON DELETE SET NULL
);


-- ============================================================
-- 4. ER_DRILL_EVENTS
--    Timestamped events logged during a drill.
--    Many events can belong to one drill record.
-- ============================================================
CREATE TABLE er_drill_events (
    id                  INT             PRIMARY KEY IDENTITY(1,1),
    drill_id            INT             NOT NULL,
    event_time          VARCHAR(50)     NULL,               -- e.g. '14:35' — use TIME if strict sorting needed
    event_description   NVARCHAR(MAX)   NOT NULL,
    created_by_email    VARCHAR(255)    NULL,               -- Captured from session at time of entry
    created_at          DATETIME2       NOT NULL DEFAULT GETUTCDATE(),

    CONSTRAINT fk_events_drill
        FOREIGN KEY (drill_id)
        REFERENCES er_drill_records(id)
        ON DELETE CASCADE
);


-- ============================================================
-- 5. ER_DRILL_ACTIONS
--    Follow-up action items raised after a drill.
--    Many actions can belong to one drill record.
-- ============================================================
CREATE TABLE er_drill_actions (
    id                  INT             PRIMARY KEY IDENTITY(1,1),
    drill_id            INT             NOT NULL,
    action_description  NVARCHAR(MAX)   NOT NULL,
    action_owner        VARCHAR(255)    NULL,
    action_status       VARCHAR(50)     NOT NULL DEFAULT 'Open',
                                                            -- 'Open', 'In Progress', 'Closed'
    due_date            DATE            NULL,
    created_by_email    VARCHAR(255)    NULL,
    created_at          DATETIME2       NOT NULL DEFAULT GETUTCDATE(),

    CONSTRAINT fk_actions_drill
        FOREIGN KEY (drill_id)
        REFERENCES er_drill_records(id)
        ON DELETE CASCADE
);


-- ============================================================
-- 6. ER_DRILL_ATTACHMENTS
--    Supporting files (photos, reports) linked to a drill.
--    Files are stored in blob/file storage; only the URL
--    reference and metadata are stored here.
-- ============================================================
CREATE TABLE er_drill_attachments (
    id                  INT             PRIMARY KEY IDENTITY(1,1),
    drill_id            INT             NOT NULL,
    caption             VARCHAR(500)    NULL,
    file_url            NVARCHAR(1000)  NOT NULL,           -- Blob storage or file server URL
    file_name           VARCHAR(255)    NULL,
    file_size_kb        INT             NULL,
    mime_type           VARCHAR(100)    NULL,               -- e.g. 'image/jpeg', 'application/pdf'
    created_by_email    VARCHAR(255)    NULL,
    created_at          DATETIME2       NOT NULL DEFAULT GETUTCDATE(),

    CONSTRAINT fk_attachments_drill
        FOREIGN KEY (drill_id)
        REFERENCES er_drill_records(id)
        ON DELETE CASCADE
);


-- ============================================================
-- INDEXES
-- ============================================================

-- User list
CREATE INDEX idx_userlist_email            ON er_drill_user_list    (email);
CREATE INDEX idx_userlist_rig_role         ON er_drill_user_list    (rig, role);
CREATE INDEX idx_userlist_active           ON er_drill_user_list    (active_status);

-- Role history
CREATE INDEX idx_rolehistory_user          ON er_drill_role_history (user_id);
CREATE INDEX idx_rolehistory_rig_role_date ON er_drill_role_history (rig, role, effective_from);
CREATE INDEX idx_rolehistory_active        ON er_drill_role_history (effective_to)
                                           WHERE effective_to IS NULL; -- Filtered index: current rotations only

-- Drill records
CREATE INDEX idx_records_status            ON er_drill_records      (status);
CREATE INDEX idx_records_sto_user          ON er_drill_records      (sto_user_id);
CREATE INDEX idx_records_be_user           ON er_drill_records      (be_user_id);
CREATE INDEX idx_records_oim_user          ON er_drill_records      (oim_user_id);
CREATE INDEX idx_records_created           ON er_drill_records      (created_at DESC);

-- Child tables
CREATE INDEX idx_events_drill_id           ON er_drill_events       (drill_id);
CREATE INDEX idx_actions_drill_id          ON er_drill_actions      (drill_id);
CREATE INDEX idx_actions_status            ON er_drill_actions      (action_status);
CREATE INDEX idx_actions_due_date          ON er_drill_actions      (due_date);
CREATE INDEX idx_attachments_drill_id      ON er_drill_attachments  (drill_id);


-- ============================================================
-- END OF SCHEMA
-- ============================================================
