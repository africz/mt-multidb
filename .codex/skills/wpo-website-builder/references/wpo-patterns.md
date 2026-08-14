# WPO and WPBakery Implementation Patterns

## Contents

1. Discovery checklist
2. Component boundaries
3. Component anatomy
4. Editable-content coverage
5. Repeaters and structured content
6. Page seeding and legacy migration
7. JavaScript across section components
8. CSS and visual fidelity
9. Admin validation
10. Frontend validation
11. Common failure modes
12. Commit boundaries

## 1. Discovery Checklist

Before editing, locate:

- all applicable `AGENTS.md` files;
- the parent and active child themes;
- the WPO component directories and registration hooks;
- plugin-owned components and their Design Studio controls;
- page seed, setup, import, or synchronization scripts;
- current page content in WordPress;
- product documentation used as the content source of truth;
- reference screenshots or a reference site used as the visual source of truth;
- existing CSS variables, spacing rules, breakpoints, fonts, and reusable wrappers.

Inspect before assuming. A plugin may already own the exact slider, phone frame, form, menu, chat surface, or privacy control needed by the design.

## 2. Component Boundaries

Use one component per independently meaningful page section. Typical boundaries are:

- hero;
- feature pillars;
- searchable catalog;
- workflow;
- pricing plans;
- comparison table;
- privacy chapters;
- FAQ;
- support/contact panel;
- closing CTA;
- footer.

Each section belongs in its own WPO row and column. This makes ordering, visibility, spacing, and content editable without forcing an administrator into a large opaque dialog.

Avoid both extremes:

- Do not create one shortcode for the entire page.
- Do not create tiny components for every decorative span when one coherent section editor is clearer.

## 3. Component Anatomy

Keep defaults, parsing, rendering, registration, styles, and behavior distinct.

Recommended flow:

1. A defaults function contains approved initial content.
2. The shortcode merges defaults with attributes.
3. Repeater parsers normalize and sanitize rows.
4. The renderer emits scoped semantic markup.
5. `vc_map` exposes every editable value.
6. The child theme enqueues scoped CSS and JavaScript.

Register custom components on `vc_before_init` and include at least:

```php
vc_map(array(
    'name' => esc_html__('Project / Section name', 'child-theme'),
    'base' => 'wpo_project_section',
    'category' => esc_html__('WPO Components', 'child-theme'),
    'params' => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Heading', 'child-theme'),
            'param_name' => 'heading',
            'admin_label' => true,
        ),
        array(
            'type' => 'textarea',
            'heading' => esc_html__('Description', 'child-theme'),
            'param_name' => 'description',
        ),
        array(
            'type' => 'textfield',
            'heading' => esc_html__('Extra class name', 'child-theme'),
            'param_name' => 'el_class',
            'group' => esc_html__('Extra', 'child-theme'),
        ),
        array(
            'type' => 'css_editor',
            'heading' => esc_html__('CSS Box', 'child-theme'),
            'param_name' => 'css',
            'group' => esc_html__('Design Options', 'child-theme'),
        ),
    ),
));
```

Collect the WPBakery custom CSS class with `vc_shortcode_custom_css_class()` and sanitize extra class names before applying them.

## 4. Editable-Content Coverage

Inventory every visible or assistive string in the renderer and JavaScript. Give each one an admin-owned source:

- headings, descriptions, eyebrows, labels, and badges;
- CTA labels and destinations;
- menu labels when they are not managed by WordPress menus;
- card titles, descriptions, numbers, and metadata;
- status counters, singular/plural labels, and search result templates;
- empty, unavailable, validation, and error messages;
- placeholders, tooltips, `aria-label` values, and image alternative text;
- JavaScript-generated labels and fallbacks;
- plugin profile keys, form IDs, slide lists, and media references.

Defaults in PHP are acceptable when the same values are exposed in `vc_map`. Hidden renderer literals are not editable.

Avoid visible fallback strings in JavaScript. Put editable copy in `data-*` attributes and read it from the component:

```js
const singular = root.dataset.itemSingular || '';
const plural = root.dataset.itemPlural || '';
label.textContent = `${count} ${count === 1 ? singular : plural}`;
```

Plugin-rendered content should remain editable through that plugin's own admin interface. Expose only the integration fields and surrounding copy in the WPO component.

## 5. Repeaters and Structured Content

Use `param_group` for cards, steps, FAQs, routes, metrics, policy chapters, comparison groups, and feature systems. Populate the map's `value` so opening an unsaved seeded component displays the approved defaults.

WPBakery-compatible default encoding:

```php
function project_encode_param_group(array $rows): string
{
    return urlencode((string) wp_json_encode(
        array_values($rows),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    ));
}
```

Parse with `vc_param_group_parse_atts()`, fall back to defaults when the attribute is absent, limit row counts, sanitize every known field, and reject rows missing the required key.

For dense nested data, use a documented multiline format inside a repeater field, such as:

```text
free|Visible screenshot capture
plus|Scrolling screenshot capture
```

Use this only when another nested `param_group` would make the editor less usable. Explain the delimiter in the field description.

Use `wp_kses_post()` only for fields intentionally allowed to contain approved HTML. Use text or textarea sanitization for ordinary content.

## 6. Page Seeding and Legacy Migration

Seed each section as a separate WPO component:

```text
[wpo_vc_row full_width="stretch_row_content_no_spaces" el_class="project-row"]
[wpo_vc_column width="1/1"]
[wpo_project_hero]
[/wpo_vc_column]
[/wpo_vc_row]

[wpo_vc_row full_width="stretch_row_content_no_spaces" el_class="project-row"]
[wpo_vc_column width="1/1"]
[wpo_project_features]
[/wpo_vc_column]
[/wpo_vc_row]
```

Do not place a large custom HTML document in page content.

When migrating a legacy page shortcode:

1. Keep its renderer temporarily as a compatibility source if useful.
2. Create section-level shortcodes.
3. Seed the page with the new components.
4. Hide or remove the legacy component from the WPBakery picker.
5. Verify live page content contains the expected section tags and zero legacy tags.
6. Remove compatibility code only when no saved content depends on it.

## 7. JavaScript Across Section Components

Splitting one page into section components changes DOM ownership. A hero search may need to filter cards or FAQ entries rendered by sibling components.

Do not assume all interactive records are descendants of the initiating component. Resolve a stable page root first:

```js
const pageRoot = root.closest('.wpo-content') || document;
const records = [...pageRoot.querySelectorAll('[data-searchable]')];
```

Initialize every component instance independently, scope event listeners, and avoid duplicate IDs. Put editable dynamic copy in `data-*` attributes. Preserve keyboard behavior and live-region announcements.

## 8. CSS and Visual Fidelity

Establish baseline screenshots before restructuring. Compare after each major implementation pass at desktop and a narrow mobile viewport.

Match:

- content shell width and edge insets;
- section height and vertical rhythm;
- column proportions and object scale;
- typography family, size, weight, line height, and wrapping;
- card gaps, padding, borders, and radii;
- background graphics, connectors, and decorative layers;
- responsive stacking and mobile spacing.

Do not solve overflow by narrowing the entire page or compressing content toward the center. Fix the responsible component.

WPO/WPBakery may emit inline zero padding on `.vc_column-inner`. Apply card or panel padding to a dependable child wrapper when the inline rule wins:

```css
.project-card > .vc_column-inner > .wpb_wrapper {
    padding: var(--card-padding);
}
```

For connector lines behind labels, mask the line with the section background and remove that mask at breakpoints where the connector is hidden. Validate that the mask does not create a contrasting block on mobile cards.

Use existing design variables and Design Studio controls. Add project variables only when no editable token already exists.

## 9. Admin Validation

Verify actual WordPress registration, not only source code.

After loading WordPress and firing `vc_before_init`:

- inspect each component with `WPBMap::getShortCode()`;
- assert required parameter names are present;
- parse default `param_group` values and assert expected row counts;
- render components with unique marker values and assert the markers appear;
- count page components in saved `post_content`;
- confirm legacy monolithic tags are absent;
- confirm page rows and columns remain separately editable.

Useful coverage inventory per page:

- component count;
- top-level control count;
- repeater count;
- default repeater row count;
- nested field count.

This catches the common situation where PHP defaults render correctly but the administrator still cannot edit them.

## 10. Frontend Validation

At minimum, validate desktop and mobile widths.

Check:

- HTTP success for every public route;
- PHP syntax, JavaScript syntax, and `git diff --check`;
- one page-level `h1`;
- no horizontal document overflow;
- no broken images or failed network requests;
- no duplicate IDs;
- no text below the repository's minimum size;
- no console or page errors;
- expected component counts;
- search/filter empty states and resets;
- sliders and their selected state;
- FAQ/privacy disclosure expansion and collapse;
- form and assistant integration availability.

For screenshot comparisons:

1. Use identical viewport and device scale.
2. Use reduced motion where possible.
3. Select the same slider state.
4. wait for `networkidle`;
5. await `image.decode()` for all images;
6. capture full-page or section screenshots;
7. compare dimensions and changed-pixel regions;
8. visually inspect meaningful differences.

Dynamic CAPTCHA content and font anti-aliasing can change pixels without moving layout. Explain such differences instead of ignoring them.

## 11. Common Failure Modes

- **One component owns the whole page:** split by major section.
- **Frontend looks correct but admin fields are missing:** expose renderer defaults in `vc_map`, including repeaters.
- **Only headings are editable:** audit all visible, dynamic, error, and accessibility text.
- **Design drifts after componentization:** preserve markup classes and defaults, then compare against baseline.
- **Search stops finding sibling cards:** query from a stable page root rather than the initiating section.
- **Phone or slider appears blank in screenshots:** await media decoding and verify the plugin instance/profile.
- **Cards touch borders:** account for WPBakery inline padding and apply inset to the correct wrapper.
- **Mobile gets dark text masks or connector remnants:** reset desktop-only masking at the breakpoint.
- **Generated database noise enters the commit:** stage the child-theme/source files explicitly.

## 12. Commit Boundaries

Before committing:

- inspect `git status`, the current branch, and remotes;
- distinguish source changes from generated dumps and concurrent work;
- stage explicit project paths rather than all files when unrelated changes exist;
- run syntax checks and `git diff --cached --check`;
- report intentionally uncommitted files after pushing.
