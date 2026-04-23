<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base class for models stored in the central (landlord) database.
 *
 * In a stancl/tenancy multi-database setup, the default DB connection
 * is switched to the tenant's own database when a tenant is initialized.
 * Any model that must remain on the central DB — even when accessed from
 * a tenant HTTP request — should extend this class instead of Model.
 *
 * IMPORTANT: If your central database connection is NOT named 'mysql',
 * change the value below to match the connection name in config/database.php.
 */
abstract class CentralModel extends Model
{
    protected $connection = 'mysql';
}
