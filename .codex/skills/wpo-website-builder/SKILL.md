---
name: wpo-website-builder
description: Build, redesign, and audit WordPress websites that use WPressOne WPO components and WPBakery. Use for WPO page construction, converting opaque page shortcodes or raw HTML into section-level editable components, exposing all visible copy and repeatable content in WPBakery, preserving a supplied design across desktop and mobile, synchronizing seeded pages, or diagnosing frontend and admin design drift.
---

# WPO Website Builder

Build visually exact WordPress pages that remain genuinely editable in WPO/WPBakery. Treat frontend quality and admin usability as equal requirements.

## Operating Contract

- Read the repository `AGENTS.md` files before editing.
- Keep project customization in the active child theme. Do not modify the WPressOne parent theme.
- Build page structure with WPO rows, columns, and components.
- Prefer existing WPO/plugin components and Design Studio controls.
- Create reusable child-theme WPO components only when the library lacks the required element.
- Never solve a full page with one opaque custom component or one-off raw HTML in page content.
- Make every visible string editable, including labels, badges, buttons, empty states, status text, accessibility text, and JavaScript-generated copy.
- Preserve the supplied design rather than approximating it.

## Workflow

### 1. Establish the Sources of Truth

Inspect the active theme, child theme, WPO component library, relevant plugins, page seed/sync code, existing WordPress content, and repository rules. Locate the product-content source and the visual reference separately; do not infer product features from the reference layout.

Capture baseline screenshots before structural work. Use the same viewport, motion settings, state, and loaded media for later comparisons.

### 2. Design the Admin Component Tree

Map each major visual section to its own WPO component and place each component in a WPO row and column. Keep headings, cards, FAQs, policies, metrics, routes, and similar collections as editable repeaters.

Use existing plugin components for specialist interfaces such as sliders, phones, forms, chat, menus, and consent controls. Expose the integration settings on the containing component instead of duplicating plugin markup.

### 3. Implement Editable Components

For each component:

- Define stable defaults that reproduce the approved design.
- Render from shortcode attributes, not hidden literals.
- Register meaningful WPBakery fields, groups, `admin_label` values, visibility controls, extra classes, and Design Options.
- Use `param_group` for repeated items and provide populated defaults.
- Expose URLs, media/profile identifiers, dynamic labels, singular/plural forms, error messages, and ARIA labels.
- Sanitize input by field type and escape output at the final context.
- Keep component CSS and JavaScript reusable and scoped.

Read [references/wpo-patterns.md](references/wpo-patterns.md) when implementing components, repeaters, page seeds, or validation.

### 4. Preserve Layout Fidelity

Match section width, height, alignment, typography, spacing, and responsive behavior against the reference. Do not make content narrower, center it, compress it, or substitute simplified visuals unless the reference requires that change.

Respect repository design tokens. Account for WPBakery inline styles such as `.vc_column-inner { padding: 0 !important; }` by placing visual padding on a reliable inner wrapper.

### 5. Synchronize WordPress Safely

Update source-controlled page seed definitions to use the section components. Run the repository's sync/apply command only after source changes validate. Confirm the database page tree contains separate section shortcodes and no legacy monolith.

Do not treat a generated database dump as source code unless repository policy explicitly requires committing it.

### 6. Verify Admin and Frontend Behavior

Do not stop after checking `vc_map` source. Load WordPress and verify registered maps, expected parameters, repeater defaults, and custom marker rendering.

Audit desktop and mobile pages for:

- exact or explained visual differences from baseline;
- horizontal overflow, clipping, and unintended compression;
- missing images, duplicate IDs, and invalid heading structure;
- minimum readable text sizes and repository spacing rules;
- console errors, page errors, and failed requests;
- filters, search, sliders, disclosures, forms, and cross-section interactions.

Wait for image decoding before screenshots so lazy media does not create false visual regressions.

## Completion Gate

Consider the task complete only when:

- each major section is independently editable in WPBakery;
- all public copy has an admin-owned source;
- the page seed and live WordPress content use WPO rows, columns, and section components;
- desktop and mobile visuals match the approved design;
- component maps, repeaters, interactions, syntax, and live routes pass validation;
- unrelated worktree changes remain untouched.
