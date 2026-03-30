<?php
namespace App;

/**
 * Project scoping hook for multi-tenant apps. This scaffold does not scope by project;
 * always returns null (no restriction). Reintroduce DB-backed logic when you add projects.
 */
class UserProjects
{
    public static function allowedProjectIds(): ?array
    {
        return null;
    }
}
