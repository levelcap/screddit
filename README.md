# Screddit: Reddit Scraper

Scrapes subreddit posts for ID, author, title, content, upvotes and reply-to fields

## Usage

### Command Line
php cl.php -mMAX_RESULTS -sSUBREDDIT -rREPLIES_BOOLEAN

Examples:
php cl.php -m1000 -sgaming -rtrue will scrape 1000 posts from the r/gaming subreddit and fetch the comments for each post
php cl.php -m500 -sgaming -rfalse will scrape 500 posts from the r/gaming subreddit without comments

### Web
Navigating to http://<yourserver>/screddit/index.php without URL parameters will generate CSV files for up to 100k posts from the /r/minecraft subreddit.  Hitting the same URL while a job is in process will display the message: "Work is in progress on subreddit minecraft, check back here for the zip file when it has completed."

Once it is done, that same page will have a link to the completed zip file.

You can also pass in the following URL parameters:
sub: Changing this will change the subreddit that you are going to get posts from, so

http://<yourserver>/screddit/?sub=news

Will create 100k (or until it runs out) post files from the /r/news subreddit.

max: Pass this in if you want to change the maximum number of results.  This is handy for testing, or if you know you want a smaller result / faster run.

http://<yourserver>/screddit/?max=100

Will create 100 files from the /r/minecraft subreddit.

delete: Pass this to delete an existing zip so you can re-run it after it's completed.  I don't have any hooks to stop a running process yet, so if you delete something mid-run it'll keep going, you'd just lose whatever progress had already been made.

http://<yourserver>/screddit/?delete=true 

Will delete any existing /r/hearthstone files

stop: Pass this to stop all current work

These are best in combination, so if you wanted to delete your run of /r/news you could pass

http://<yourserver>/screddit/?delete=true&sub=news

Or if you wanted to do a quick run of a different subreddit

http://<yourserver>/screddit/?max=100&sub=funny

Only one job can be run at a time to avoid running into Reddit's API limits.

## License

This software is licensed under the MIT license
