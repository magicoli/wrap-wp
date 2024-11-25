# W.R.A.P. - Web Reel Automated Publishing

The W.R.A.P. plugin is a complement to [W.R.A.P. by Magiiic](https://wrap.rocks) platform, an online video-centric publishing service that includes fast processing, optimized streaming, editing, and organizing of provided content.

This plugin serves as the administration tool for the service, allowing the management of user authentication on the external platform, groups (clients, companies, etc.), and projects.

At this stage, the service provides:
- an authentication API usable by an Apache server with standard modules
- management of accredited users by group

## Features

- **User Authentication**: Manage user authentication on the external platform.
- **Group Management**: Organize users into groups (clients, companies, etc.) and manage their access.
- **Project Management**: Handle various projects associated with different groups.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wrap` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->WRAP screen to configure the plugin.

## Usage

### Authentication API

To use the authentication API, add the following lines in `.htaccess` file, inside the folder you want to restrict to a group of users, replacing your-group with the base name of the folder, and yourdomain.org with the adress of your wordpress website. A prefilled template is provided in the plugin for each group, i.e. each subsite.

```.htaccess
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTP_COOKIE} !wrap_auth_your-group=1
  RewriteRule ^(.*)$ https://yourdomain.org/wrap-auth/?redirect_to=%{REQUEST_SCHEME}://%{HTTP_HOST}%{REQUEST_URI} [L,R=302]
</IfModule>
```
