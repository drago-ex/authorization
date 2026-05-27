--
--  Simple dynamic access control list management.
-- -----------------------------------------------
SET NAMES utf8mb4;
SET time_zone = '+00:00';

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS permissions_roles_view;
DROP VIEW IF EXISTS permissions_view;
DROP VIEW IF EXISTS users_roles_view;

DROP TABLE IF EXISTS users_roles;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS privileges;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS = 1;

--
--  Database privileges
-- --------------------
CREATE TABLE privileges (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(40) NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_privileges_name (name)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO privileges (id, name) VALUES
    (1, '*'),
    (2, 'default');

--
--  Database resources
-- -------------------
CREATE TABLE resources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(40) NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_resources_name (name)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO resources (id, name) VALUES
    (1, 'Front:Home'),
    (2, 'Backend:Admin'),
    (3, 'Backend:Sign'),
    (4, 'Backend:Permissions');

--
--  Database roles
-- ----------------
CREATE TABLE roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(40) NOT NULL,
    parent INT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO roles (id, name, parent) VALUES
    (1, 'guest', 0),
    (2, 'member', 1),
    (3, 'admin', 2);

--
--  Database permissions
-- ----------------------
CREATE TABLE permissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    privilege_id INT UNSIGNED NOT NULL,
    allowed TINYINT(1) NOT NULL,

    PRIMARY KEY (id),

    UNIQUE KEY uq_permissions (
        role_id,
        resource_id,
        privilege_id
    ),

    CONSTRAINT fk_permissions_role
        FOREIGN KEY (role_id)
            REFERENCES roles (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

    CONSTRAINT fk_permissions_resource
        FOREIGN KEY (resource_id)
            REFERENCES resources (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

    CONSTRAINT fk_permissions_privilege
        FOREIGN KEY (privilege_id)
            REFERENCES privileges (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO permissions (
    id,
    role_id,
    resource_id,
    privilege_id,
    allowed
) VALUES
    (1, 1, 1, 1, 1),
    (2, 1, 2, 2, 1),
    (3, 1, 3, 1, 1),
    (4, 3, 2, 1, 1);

--
--  Database settings
-- -------------------
CREATE TABLE settings (
    name VARCHAR(100) NOT NULL,
    value VARCHAR(255) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

--
--  Database users
-- ----------------
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(60) NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

--
--  Database users_roles
-- ----------------------
CREATE TABLE users_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (user_id, role_id),

    CONSTRAINT fk_users_roles_user
        FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

    CONSTRAINT fk_users_roles_role
        FOREIGN KEY (role_id)
            REFERENCES roles (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

--
--  View permissions_roles_view
-- -----------------------------
CREATE VIEW permissions_roles_view AS
SELECT
    roles.id,
    roles.name,
    roles.parent
FROM roles
WHERE roles.id IN (
    SELECT DISTINCT permissions.role_id
    FROM permissions
    WHERE roles.name <> 'admin'
);

--
--  View permissions_view
-- -----------------------
CREATE VIEW permissions_view AS
SELECT
    p.id,
    r.name AS resource,
    pr.name AS privilege,
    ro.name AS role,
    p.allowed
FROM permissions p
    LEFT JOIN resources r
        ON p.resource_id = r.id
    LEFT JOIN privileges pr
        ON p.privilege_id = pr.id
    LEFT JOIN roles ro
        ON p.role_id = ro.id;

--
--  View users_roles_view
-- -----------------------
CREATE VIEW users_roles_view AS
SELECT
    u.id AS user_id,
    u.username,
    r.name AS role
FROM users_roles ur
    LEFT JOIN users u
        ON ur.user_id = u.id
    LEFT JOIN roles r
        ON ur.role_id = r.id;
