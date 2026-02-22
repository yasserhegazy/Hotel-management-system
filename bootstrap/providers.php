<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Tenants\Providers\TenantsServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
];
