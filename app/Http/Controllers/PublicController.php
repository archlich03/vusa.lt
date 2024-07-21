<?php

namespace App\Http\Controllers;

use App\Actions\GetAliasSubdomainForPublic;
use App\Models\MainPage;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PublicController extends Controller
{
    protected Tenant $tenant;

    protected string $subdomain;

    public function __construct()
    {
        /**
         * Every public page requires an 'alias', which is basically the shortname of a tenant.
         * Alias may decide in the controller, what kind of information is displayed.
         *  */
        [$alias, $subdomain] = GetAliasSubdomainForPublic::execute();

        // When we have the final alias, get the tenant that will be used in all of the public controllers
        $this->tenant = Tenant::where('alias', $alias)->first();

        // We also need to use the subdomain in the public controllers
        $this->subdomain = $subdomain;

        // Subdomain and alias won't be different, except when alias = 'vusa', then subdomain = 'www'
        Inertia::share('tenant', $this->tenant->only(['id', 'shortname', 'alias', 'type']) +
            ['subdomain' => $subdomain]
        );
    }

    protected function getBanners()
    {
        $banners = Cache::remember('banners-'.$this->tenant->id, 60 * 30, function () {

            $banners = Tenant::where('alias', 'vusa')->first()->banners()->inRandomOrder()->where('is_active', 1)->get();

            if ($this->tenant->type !== 'pagrindinis') {
                $banners = $this->tenant->banners()->inRandomOrder()->where('is_active', 1)->get()->merge($banners);
            }

            return $banners;
        });

        Inertia::share('tenant.banners', $banners);
    }

    protected function getTenantLinks()
    {
        $mainPage = MainPage::query()->where([['tenant_id', $this->tenant->id], ['lang', app()->getLocale()]])->orderBy('order')->get(['id', 'link', 'text']);

        Inertia::share('tenant.links', $mainPage);
    }

    // This is mostly used for default sharing, other cases likes pages and news link to other URLs
    protected function shareOtherLangURL($name, ?string $subdomain = null, $calendarId = null)
    {
        Inertia::share('otherLangURL',
            route($name,
                [
                    'lang' => $this->getOtherLang(),
                    'calendar' => $calendarId,
                    'subdomain' => $subdomain,
                ])
        );
    }

    protected function getOtherLang()
    {
        return app()->getLocale() === 'lt' ? 'en' : 'lt';
    }
}
