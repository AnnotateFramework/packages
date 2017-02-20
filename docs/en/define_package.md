Defining package
================

For example we will define jQueryUI package. This package depends on jQuery and will have some variants.

1. Create `%appDir%/addons/packages/jQueryUI` directory
2. Download jQueryUI and paste its files to that directory
3. Create `jQueryUI.package.neon` file

File structure
--------------

Required items in package definition are:

- name
- version
- variants

jQueryUI package definition could look like this:

```yml
name: jQueryUI
version: 1.11.1
variants:
    js_only:
        scripts:
            - @jquery-ui.min.js
    default < js_only:
        styles:
            - @jquery-ui.min.css
    ui-darkness < js_only:
        styles:
            - @themes/ui-darkness/jquery-ui.min.css
dependencies:
    jQuery:
        version: 1.6
```

Variants
--------

Package variants definitions support basic inheritance. In the defined package the `default` and `ui-darkness` variants extend `js_only` variant. This way
we can define variant for each jQueryUI theme. When you override item in child variant it will merge its contents (instead of overriding as Neon normally does).
Because of this behaviour `js_only` variant is needed in this case.

Dependencies
------------

Item `dependencies` the package definition is list of package names which defined package depends on. You can define also minimal version of package or variant.

Next steps
----------

- [require package](require_package.md) [TODO]
