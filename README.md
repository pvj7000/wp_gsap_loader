# GSAP Script Loader

<img width="1213" height="714" alt="wp-gsap" src="https://github.com/user-attachments/assets/67b999a3-fe9c-4e1d-9bd8-2a1acb9b1bc4" />

## Purpose
The **GSAP Script Loader** plugin provides a streamlined way for WordPress developers to manage and enqueue GSAP libraries. Instead of manually registering scripts in `functions.php`, this plugin offers a visual interface to toggle specific GSAP modules on and off, loading them directly from a high-performance CDN (`cdnjs`).

## What is GSAP?
The GreenSock Animation Platform (GSAP) is a robust JavaScript library for building high-performance animations that work in every major browser. It delivers professional-grade control and flexibility, allowing developers to animate CSS, SVG, Canvas, React, Vue, WebGL, and more with ease.

## Scope
This plugin focuses strictly on **script management**.

- **In Scope**:
    - Listing available GSAP 3 plugins.
    - Enabling/Disabling specific plugins via an admin UI.
    - Handling dependencies (e.g., ensuring GSAP Core loads if ScrollTrigger is active).
    - Enqueuing scripts in the site footer for performance.
- **Out of Scope**:
    - Providing a GUI for *creating* animations.
    - Generating animation code.
    - Bundling local script files (scripts are loaded via CDN).

## Project Structure
The plugin is organized to keep logic, data, and presentation separate:

```
gsap-script-loader/
├── gsap-script-loader.php       # Main Plugin File: Handles hooks, initialization, and backend enqueue logic.
├── includes/
│   └── plugins-data.php         # Data Source: Returns an array of available plugins, CDN URLs, and metadata.
├── admin/
│   ├── settings-page.php        # View: Renders the HTML for the admin settings page.
│   ├── ui.css                   # Styles: Custom CSS for the admin card grid and categories.
│   └── ui.js                    # Interaction: Handles AJAX auto-saving and frontend dependency logic.
└── README.md                    # Documentation
```
