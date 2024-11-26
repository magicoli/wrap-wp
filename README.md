# W.R.A.P. - Web Reel Automated Publishing

The W.R.A.P. plugin is a complement to [W.R.A.P. by Magiiic](https://wrap.rocks) platform, an online video-centric publishing service that includes fast processing, optimized streaming, editing, and organizing of provided content.

This plugin serves as the administration tool for the service, allowing the management of user authentication on the external platform, groups (clients, companies, etc.), and projects.

Although the plugin was created to complement a specific application, it has been designed generically to adapt to other third-party applications, assuming they are based on the use of subdirectories of the website.

## Features

At this stage, the service provides:

- **Authentication API**: Manage user authentication on the external platform, allowing a standalone application to benefit from WordPress user management.
- **Group Management**: Organize users into groups (could be clients, companies, etc.) and manage their access.

## Roadmap
- **Project Management**: Assign new projects to groups, show projects on group page.
- **Full Wrap libary**: Integrate the current standalone Wrap project into the plugin.

## Requirements

- **A third-party web application or a part of the website not handled by WordPress**, relying on document root subfolders.
- **WordPress should be installed in a subdirectory**. Although not mandatory, a subfolder is a better approach for clarity and maintenance.

This ensures that WordPress handles only the calls to its own structure and permalinks, while existing folders and files outside the WordPress directory are handled directly by the web server or the third-party application.

## Example File Structure

Here is an example of how your file structure might look:
```
/var/www/html/
├── wp/ # WordPress installed in a subdirectory
│ ├── wp-content/
│ └── ...
├── group1/ # Third-party application protected group
│ ├── .htaccess # Authentication rules for group1
│ ├── project1/
│ └── project2/
├── group2/ # Third-party application public group
│ ├── project1/
│ ├── project2/
│ └── private/ # Protected subdirectory
│ └── .htaccess # Authentication rules subdirectory
└── ...
```

## Installation

1. Upload the plugin files to the `wp-content/plugins/wrap` directory, and activate as usual plugins.
2. Go to Admin->WRAP->Settings screen to set the base URL of your external app (could be http://yourdomain.org/ or http://yourdomain.org/myapp/), and its local path (/var/www/html/ or /var/www/html/myapp/).
3. Create groups, and assign allowed WP users.
4. In the folders you want authentication, add a `.htaccess` file with the rules provided in the related group edit page, or adjust the example here:
    ```.htaccess
    <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteCond %{HTTP_COOKIE} !wrap_auth_your-group=1
      RewriteRule ^(.*)$ https://yourdomain.org/wrap-auth/?redirect_to=%{REQUEST_SCHEME}://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]
    </IfModule>
    ```
    The first part of the URL must match an existing group, but the `.htaccess` file can be anywhere inside this group path (e.g., you can either protect the whole yourdomain.org/group1/ subsite, or leave it public and restrict access only to yourdomain.org/group1/private/).

## Usage

Users visiting a page of the third-party app will be redirected if needed to the WP login page, and brought back to the page requested on success, otherwise get an error message.

WordPress users have a profile page, where they can see the groups they belong to.

## Frequently Asked Questions

- Can I use this plugin to authenticate a third-party app that doesn't rely on local folders?

    No. That's not the purpose of this plugin. There are many extensions and libraries to share authentication between different platforms.

- Must the third-party app be part of the same website?

    Yes, the plugin relies on same-site cookies.

- Can I install WordPress in a subfolder and still let it manage the main website?

    Yes, WordPress can be installed in a subfolder and still handle the site as if it was in the root folder. See WordPress documentation for details.
