-- ============================================================================
--  Employee OT : Date-wise OT assignment
--  Database : prims_hospital (MySQL 8 / MariaDB 10.4+)
--
--  Run these statements once, in this order, on the application database.
--  Nothing here deletes or rewrites existing OT data.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. rl_employee_ot_tbl
--    New column that records HOW the OT entry was captured:
--      'manual' = the existing Manage OT screen (days / hours / rate)
--      'dates'  = the new Assign OT screen (one row per date in the child table)
--    Every existing row automatically becomes 'manual', so old behaviour is
--    completely unchanged.
-- ----------------------------------------------------------------------------
ALTER TABLE `rl_employee_ot_tbl`
    ADD COLUMN `ot_source` VARCHAR(20) NOT NULL DEFAULT 'manual'
    COMMENT 'manual = day/hour entry, dates = date-wise OT assignment'
    AFTER `ot_amount`;

-- ----------------------------------------------------------------------------
-- 2. rl_employee_ot_dates_tbl
--    One row per selected OT date.
--
--    ot_id      -> rl_employee_ot_tbl.id (the monthly OT header for that employee)
--    ot_date    -> the actual date the overtime was worked
--    ot_amount  -> the per-day OT amount agreed for THAT date (kept as a
--                  snapshot, so later rate changes never rewrite history)
--
--    The unique key guarantees one OT entry per employee per calendar date.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rl_employee_ot_dates_tbl` (
    `id`          bigint unsigned NOT NULL AUTO_INCREMENT,
    `client_id`   bigint unsigned NOT NULL,
    `employee_id` bigint unsigned NOT NULL,
    `ot_id`       bigint unsigned NOT NULL,
    `ot_date`     date NOT NULL,
    `ot_amount`   decimal(12,2) NOT NULL DEFAULT '0.00',
    `remarks`     varchar(500) DEFAULT NULL,
    `status`      tinyint(1) NOT NULL DEFAULT '1',
    `trash`       tinyint(1) NOT NULL DEFAULT '0',
    `created_by`  bigint unsigned DEFAULT NULL,
    `updated_by`  bigint unsigned DEFAULT NULL,
    `created_at`  timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_client_employee_ot_date` (`client_id`, `employee_id`, `ot_date`),
    KEY `idx_ot_id` (`ot_id`),
    KEY `idx_client_employee_date` (`client_id`, `employee_id`, `ot_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------------------------------------------------------
-- 3. Lookup index for the payroll / OT screens, which always filter by
--    client + employee + month + year.
-- ----------------------------------------------------------------------------
ALTER TABLE `rl_employee_ot_tbl`
    ADD INDEX `idx_client_employee_month` (`client_id`, `employee_id`, `ot_year`, `ot_month`);
