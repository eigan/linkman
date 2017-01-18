# Linkman
A concept for organizing a large collection of files, mainly photos and videos.

#### Things to do before 1.0
- Rename project to something that makes sense..
- Auth
- Sharing
- Web api
- More plugins
- Write tests
- Documentation
- Make it installable
- Make it updateable

## Install
```bash
git clone https://github.com/eigan/linkman.git && composer install
```

#### Update
```bash
git pull
```

## Usage
```sh
# Initialize the sqlite db file in current directory
$ linkman init

# Add a folder
$ linkman mount ~/pictures pictures

# Sync all files in all mounts (add meta to db)
$ linkman sync
#> Calling hook 'sync' on new/updated files
#> Hooks read every file and parses data (exif, adds tags etc)

# Contents
# Linkman separates between a File and FileContent (checksum). Which means
# that if two files are equal, they will share the same data (tags, etc)

$ linkman contents
# > Requires either an --output option, or --action to do stuff with the content

# Case: Rename all files to follow a particular format
$ linkman contents --action-rename="{Y}-{m}/{d}/{filename}.{ext}"
                                  # 2016-11/10/DCIM_10.jpg

# Case: Output latest 10 contents to a cli-table
$ linkman contents --order-latest --filter-limit="10" --output-table

```

### Current commands
use `./bin/linkman help <command>`.
```
contents
init
mount
mounts
sync
help
```

#### Current `contents` options
An `--output-X` option is required.

```bash
./bin/linkman contents [option] --output-table
```

```
--action-album=""    Make album of the selection
--action-rename=""   Renames the contents
--action-tag=""      Adds a tag
--filter-created=""  Filters by date
--filter-day=""      Contents on this day
--filter-limit=""    Limit the results
--filter-name=""     Match the path of the file
--filter-month=""    Contents in this month
--filter-offset=""   Offset the results
--filter-tag=""      Filter by comma separated list of tags
--filter-type=""     Only of given type
--filter-year=""     Contents in this year
--order-created=""   Order by created date
--order-latest       Order by latest (modified)
--order-modified=""  Order by modified date
--output-json        Sends json contents
--output-table       Basic table with info
```

### Current API
Start dev API server with `php -S localhost:8080 api.php` in `public/` folder.
```
GET  /api/v1
GET  /api/v1/albums
POST /api/v1/albums
GET  /api/v1/albums/:albumId
GET  /api/v1/albums/:albumId/contents
POST /api/v1/albums/:albumId/contents
GET  /api/v1/browse
GET  /api/v1/calendar
GET  /api/v1/calendar/:year
GET  /api/v1/calendar/:year/contents
GET  /api/v1/calendar/:year/months
GET  /api/v1/calendar/:year/months/:month/contents
GET  /api/v1/calendar/:year/months/:month/days
GET  /api/v1/calendar/:year/months/:month/days/:day/contents
GET  /api/v1/contents
GET  /api/v1/contents/:contentId
POST /api/v1/contents/:contentId
GET  /api/v1/contents/:contentId/albums
POST /api/v1/contents/:contentId/favorite
GET  /api/v1/contents/:contentId/files
GET  /api/v1/contents/:contentId/tags
GET  /api/v1/contents/:contentId/raw
GET  /api/v1/files
GET  /api/v1/files/:fileId
GET  /api/v1/mounts
GET  /api/v1/mounts/:mountId
GET  /api/v1/search/contents
GET  /api/v1/tags
```

### Global options
```sh
--path="./" # Path to where we have the linkman.db file
```


## Plugins
See the [CorePlugin](src/Plugin/CorePlugin.php).

### Hooks
Hooks, and what objects you can typehint.
```
sync.file.start: $path, Linkman\Api\Api
sync:            File, FileContent
```

### Content plugins
3 types of plugins: `content action, content filter, "content query modifier"`.

```php
<?php
// Action and output has equal interfaces see ContentOutputInterface
// See Linkman\Plugin\Action
interface ContentActionInterface
{
    // Do action on a set of contents (files available in $content->getFiles())
    public function execute(array $contents, $argValue);
}

// Modifies all queries when calling for a FileContent
// We can use this to add filters or change the order
// See Linkman\Plugin\Filter and Linkman\Plugin\Order
interface ContentQueryModifierInterface
{
    public function modify(QueryBuilder $query, $argValue);
}

```

### Custom plugin/hooks
Add a `linkman.php` file in same path as `linkman.db`.

```php
<?php

use Linkman\Linkman;
use Linkman\Domain\FileContent;
use Linkman\Domain\File;

return [
    'hooks' => [
        //'sync' => function(FileContent $fileContent, File $file) {
            // Modify FileContent and/or File
        //}
    ],

    'register' => function(Linkman $linkman) {
        // Extend with a class that implements one of:
        // - ContentQueryModifierInterface
        // - ContentActionInterface
        // - ContentOutputInterface
        //
        // $linkman->use($myClass);
    }
];

```

## Plans

#### Commands I would like to add.
```sh
albums # As contents, just list albums in a table
print # Just echo out the content (for piping)
tags # List all tags and usage
size-of # Size of a particular folders, num files and store size
diff # Diff between mounts / Folders
ls mount:path # List a specific directory
link # Make a symlink where on you filesystem to a particulr mount:path/file
serve # Start api with php -S (dev)
share # Share folder / files
shares # Everything shared
```

##### Content options

```sh
--filter-hidden
--filter-visible # default though
--filter-duplicated

--action-copy # Copy all files
--action-hide

--output-gif="[fps]" # Make a gif of the selection
```

