For testing the sending email about bets posts from 7days ago we use below command inorder to test our implementation :

Besure to run below command in seperate terminals :

php artisan queue:work
php artisan schedule:work

run :

php artisan tinker

Modules\Marketing\Jobs\SendBestPostsNewsletterJob::dispatch(null, true);

Press Enter, then type exit to leave Tinker. 