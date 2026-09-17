<?php

namespace GlpiPlugin\Mod;

use Glpi\Plugin\Hooks;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

final class BrandingHooks
{
    public static function init(): void
    {
        global $PLUGIN_HOOKS, $CFG_GLPI;

        // The branding stylesheet is served dynamically by a public Controller.
        // This avoids the hard-coded /plugins/<key>/ static-asset path used by
        // GLPI's generic ADD_CSS extension when the plugin is installed through
        // the Marketplace directory. The stylesheet is loaded before core CSS,
        // so its branding declarations use !important and remain deterministic.
        $cssUrl = self::getPluginWebPrefix() . '/UIBranding/BrandingCss';

        $headerTags = [
            [
                'tag' => 'link',
                'properties' => [
                    'rel'  => 'stylesheet',
                    'type' => 'text/css',
                    'href' => $cssUrl,
                ],
            ],
        ];

        if (BrandManager::isFaviconEnabled()) {
            $faviconUrl = self::getPluginWebPrefix() . '/UIBranding/Asset/favicon?v='
                . rawurlencode(BrandManager::getResourceFingerprint('favicon'));
            $headerTags[] = [
                'tag' => 'link',
                'properties' => [
                    'rel'      => 'icon',
                    'type'     => 'image/x-icon',
                    'sizes'    => '16x16',
                    'href'     => $faviconUrl,
                    'data-mod-favicon' => '1',
                ],
            ];
        }

        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['mod'] = $headerTags;
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG_ANONYMOUS_PAGE]['mod'] = $headerTags;

        // Live previews are required on the configuration page regardless of
        // whether the custom favicon feature is enabled.
        $javascript = ['uibranding.js'];
        if (BrandManager::isFaviconEnabled()) {
            $javascript[] = 'favicon.js';
        }
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['mod'] = $javascript;
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['mod'] = $javascript;

        $title = BrandManager::getCurrentTitle();
        if ($title !== '') {
            $CFG_GLPI['app_name'] = $title;
        }
    }

    private static function getPluginWebPrefix(): string
    {
        $pluginPath = str_replace('\\', '/', \Plugin::getPhpDir('mod'));
        return str_contains($pluginPath, '/marketplace/') ? '/marketplace/mod' : '/plugins/mod';
    }

}
