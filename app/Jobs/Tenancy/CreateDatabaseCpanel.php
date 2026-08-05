<?php

namespace App\Jobs\Tenancy;

use App\Services\CpanelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;

class CreateDatabaseCpanel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase|Model */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(CpanelService $cpanelService)
    {
        event(new CreatingDatabase($this->tenant));

        // Note: the status === 'pending' guard was removed from here.
        // The TenantCreated event pipeline is now guarded at the listener level
        // (TenancyServiceProvider closure), so this job is only invoked either:
        //   (a) explicitly via dispatchSync() in approveStore() — should always run, or
        //   (b) by the pipeline for non-pending tenants — also should always run.
        // Keeping the pending guard here caused approveStore() to silently skip DB
        // creation (tenant is still 'pending' during provisioning, status flips at Step 5).
        if ($this->tenant->getInternal('create_database') === false) {
            return false;
        }

        $this->tenant->database()->makeCredentials();

        // Instead of using the default DatabaseManager to execute raw CREATE DATABASE queries,
        // we use the cPanel UAPI directly to provision and permission the database.
        $dbName = $this->tenant->database()->getName();

        // If CPANEL_DOMAIN is set, we're likely in a production/staging environment where cPanel is available.
        // If it's missing (e.g., local development), we fallback to the default driver.
        if (env('CPANEL_DOMAIN')) {
            $cpanelService->provisionTenantDatabase($dbName);
        } else {
            // Local fallback
            $this->tenant->database()->manager()->createDatabase($this->tenant);
        }

        event(new DatabaseCreated($this->tenant));
    }
}
