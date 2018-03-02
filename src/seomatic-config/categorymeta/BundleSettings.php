<?php
/**
 * SEOmatic plugin for Craft CMS 3.x
 *
 * A turnkey SEO implementation for Craft CMS that is comprehensive, powerful,
 * and flexible
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

/**
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.0.0
 */

return [
    '*' => [
        'seoImageIds'        => [],
        'seoImageSource'     => 'fromAsset',
        'seoImageField'      => '',
        'twitterImageIds'    => [],
        'twitterImageSource' => 'sameAsSeo',
        'twitterImageField'  => '',
        'ogImageIds'         => [],
        'ogImageSource'      => 'sameAsSeo',
        'ogImageField'       => '',
    ],
];
