<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin registry for GSAP Script Loader.
 *
 * Notes
 * - This file intentionally contains NO hardcoded CDN URLs or versions.
 * - The main plugin resolves the latest GSAP version available on cdnjs and
 *   builds URLs dynamically.
 * - `requires` is ALWAYS an array of plugin keys (not script handles).
 */
function gsap_sl_get_plugins()
{
    return [
        'gsap-core' => [
            'name'        => 'GSAP Core',
            'handle'      => 'gsap-core',
            'filename'    => 'gsap.min.js',
            'description' => 'The core GreenSock Animation Platform. Required for all other plugins.',
            'category'    => 'Core',
            'color'       => '#0ae448',
            'required'    => false,
            'requires'    => [],
        ],

        // Scroll
        'scrolltrigger' => [
            'name'        => 'ScrollTrigger',
            'handle'      => 'gsap-scrolltrigger',
            'filename'    => 'ScrollTrigger.min.js',
            'description' => 'Create jaw-dropping scroll-based animations with minimal code.',
            'category'    => 'Scroll',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],
        'scrollto' => [
            'name'        => 'ScrollTo',
            'handle'      => 'gsap-scrollto',
            'filename'    => 'ScrollToPlugin.min.js',
            'description' => 'Scroll to any position (or element) with smooth animation.',
            'category'    => 'Scroll',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],
        'scrollsmoother' => [
            'name'        => 'ScrollSmoother',
            'handle'      => 'gsap-scrollsmoother',
            'filename'    => 'ScrollSmoother.min.js',
            'description' => 'Silky smooth scrolling with parallax effects. (Requires ScrollTrigger)',
            'category'    => 'Scroll',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core', 'scrolltrigger'],
        ],

        // UI
        'flip' => [
            'name'        => 'Flip',
            'handle'      => 'gsap-flip',
            'filename'    => 'Flip.min.js',
            'description' => 'Seamlessly animate layout changes (FLIP technique).',
            'category'    => 'UI',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],
        'draggable' => [
            'name'        => 'Draggable',
            'handle'      => 'gsap-draggable',
            'filename'    => 'Draggable.min.js',
            'description' => 'Make any element draggable with momentum functionality.',
            'category'    => 'UI',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],
        'observer' => [
            'name'        => 'Observer',
            'handle'      => 'gsap-observer',
            'filename'    => 'Observer.min.js',
            'description' => 'Normalize scroll/touch/pointer events across devices.',
            'category'    => 'UI',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],
        'inertia' => [
            'name'        => 'Inertia',
            'handle'      => 'gsap-inertia',
            'filename'    => 'InertiaPlugin.min.js',
            'description' => 'Add momentum-based motion to Draggable (formerly ThrowProps).',
            'category'    => 'UI',
            'color'       => '#00bae2',
            'requires'    => ['gsap-core'],
        ],

        // SVG
        'motionpath' => [
            'name'        => 'MotionPath',
            'handle'      => 'gsap-motionpath',
            'filename'    => 'MotionPathPlugin.min.js',
            'description' => 'Animate any element along a path (SVG, Canvas, etc.).',
            'category'    => 'SVG',
            'color'       => '#ff8709',
            'requires'    => ['gsap-core'],
        ],
        'drawsvg' => [
            'name'        => 'DrawSVG',
            'handle'      => 'gsap-drawsvg',
            'filename'    => 'DrawSVGPlugin.min.js',
            'description' => 'Progressively reveal SVG strokes.',
            'category'    => 'SVG',
            'color'       => '#ff8709',
            'requires'    => ['gsap-core'],
        ],
        'morphsvg' => [
            'name'        => 'MorphSVG',
            'handle'      => 'gsap-morphsvg',
            'filename'    => 'MorphSVGPlugin.min.js',
            'description' => 'Morph any SVG shape into another.',
            'category'    => 'SVG',
            'color'       => '#ff8709',
            'requires'    => ['gsap-core'],
        ],
        'motionpathhelper' => [
            'name'        => 'MotionPathHelper',
            'handle'      => 'gsap-motionpathhelper',
            'filename'    => 'MotionPathHelper.min.js',
            'description' => 'Visualize and edit motion paths with helper tools.',
            'category'    => 'SVG',
            'color'       => '#ff8709',
            'requires'    => ['gsap-core'],
        ],

        // Text
        'text' => [
            'name'        => 'Text',
            'handle'      => 'gsap-text',
            'filename'    => 'TextPlugin.min.js',
            'description' => 'Animate text content, simulating typing or replacement.',
            'category'    => 'Text',
            'color'       => '#a69eff',
            'requires'    => ['gsap-core'],
        ],
        'splittext' => [
            'name'        => 'SplitText',
            'handle'      => 'gsap-splittext',
            'filename'    => 'SplitText.min.js',
            'description' => 'Split HTML text into characters, words, and lines.',
            'category'    => 'Text',
            'color'       => '#a69eff',
            'requires'    => ['gsap-core'],
        ],
        'scrambletext' => [
            'name'        => 'ScrambleText',
            'handle'      => 'gsap-scrambletext',
            'filename'    => 'ScrambleTextPlugin.min.js',
            'description' => 'Cipher-like text scrambling effects.',
            'category'    => 'Text',
            'color'       => '#a69eff',
            'requires'    => ['gsap-core'],
        ],

        // Eases
        'easepack' => [
            'name'        => 'EasePack',
            'handle'      => 'gsap-easepack',
            'filename'    => 'EasePack.min.js',
            'description' => 'Extra easing functions (SlowMo, RoughEase, ExpoScaleEase).',
            'category'    => 'Eases',
            'color'       => '#f7bdf8',
            'requires'    => ['gsap-core'],
        ],
        'customease' => [
            'name'        => 'CustomEase',
            'handle'      => 'gsap-customease',
            'filename'    => 'CustomEase.min.js',
            'description' => 'Create custom eases by drawing them or copying SVG paths.',
            'category'    => 'Eases',
            'color'       => '#f7bdf8',
            'requires'    => ['gsap-core'],
        ],
        'customwiggle' => [
            'name'        => 'CustomWiggle',
            'handle'      => 'gsap-customwiggle',
            'filename'    => 'CustomWiggle.min.js',
            'description' => 'Create complex wiggle effects. (Requires CustomEase)',
            'category'    => 'Eases',
            'color'       => '#f7bdf8',
            'requires'    => ['gsap-core', 'customease'],
        ],
        'custombounce' => [
            'name'        => 'CustomBounce',
            'handle'      => 'gsap-custombounce',
            'filename'    => 'CustomBounce.min.js',
            'description' => 'Create realistic bounce effects. (Requires CustomEase)',
            'category'    => 'Eases',
            'color'       => '#f7bdf8',
            'requires'    => ['gsap-core', 'customease'],
        ],

        // Extra / Other
        'gsdevtools' => [
            'name'        => 'GSDevTools',
            'handle'      => 'gsap-gsdevtools',
            'filename'    => 'GSDevTools.min.js',
            'description' => 'Inspect and control timelines with developer tools.',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
        'cssrule' => [
            'name'        => 'CSSRule',
            'handle'      => 'gsap-cssrule',
            'filename'    => 'CSSRulePlugin.min.js',
            'description' => 'Animate pseudo-elements like ::before and ::after.',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
        'pixi' => [
            'name'        => 'Pixi',
            'handle'      => 'gsap-pixi',
            'filename'    => 'PixiPlugin.min.js',
            'description' => 'Optimized animation for PixiJS objects.',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
        'easel' => [
            'name'        => 'Easel',
            'handle'      => 'gsap-easel',
            'filename'    => 'EaselPlugin.min.js',
            'description' => 'Optimized animation for EaselJS objects.',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
        'physics2d' => [
            'name'        => 'Physics2D',
            'handle'      => 'gsap-physics2d',
            'filename'    => 'Physics2DPlugin.min.js',
            'description' => 'Physics-based movement (gravity, velocity, friction).',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
        'physicsprops' => [
            'name'        => 'PhysicsProps',
            'handle'      => 'gsap-physicsprops',
            'filename'    => 'PhysicsPropsPlugin.min.js',
            'description' => 'Drive animations with velocity and acceleration physics.',
            'category'    => 'Extra',
            'color'       => '#0ae448',
            'requires'    => ['gsap-core'],
        ],
    ];
}
