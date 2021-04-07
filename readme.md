## Supermetric Analytics API Consumer
 
> This project provides basic statistics of posts made on social
> media (supermetrics), To consume to this code one requires credentials
> provided by supermetrics

>Example Credentials are in `supermetric_credentials.php`

```php
return [
    'client_id' => 'client Id provided by supermetric',
    'email'     => 'email@supermetrics.com',
    'name'      => 'Demo Client'
];
```

### Installation and running of application

> Environment : `PHP=^8.0`

##### Steps to run the application

```shell
git clone gitURL
cd supermetrics
composer install
```
> Running the script
```shell
php consumer.php
```

> Once the scripts run successfully, you would see output like

```json
{
    "avg_char_length_post_per_month": {
        "February": 403,
        "January": 399
    },
    "longest_post_per_month": {
        "February": "pest corn civilian candle train syndrome hole question script unfair twist factor survey belief shift initiative gradient set whip kill porter plagiarize coincidence key escape pit policeman clinic pour rehabilitation excitement tune egg white climate computing credit card wake instal try highway lion absent visible direct hostile deficiency nuclear painter spell network damage tolerate disk squash boat mess mathematics pioneer corruption foreigner indication science carve brake cord retirement grudge knock flawed counter roar tired computing discrimination extinct recommendation estimate producer troop generate harmful outfit belly orchestra press relax tolerate organize option sun essay rush popular feminine blade plagiarize deserve photocopy building",
        "January": "dorm achievement alcohol rear sister reliance export seem climb relax dare lighter jurisdiction gradient dilute old tenant pour flavor railroad vegetarian reputation urine reward sword section lake quotation fireplace hiccup experiment therapist painter snub alcohol giant introduce glove innocent seller proposal mosque stress vague evening nationalist parachute host short circuit combine bullet mile shy invisible fireplace electron bracket chapter electronics freedom acceptance delay straw modernize bathroom speed ally margin heaven carve broken adventure duck corruption distant epicalyx part computer test church assessment friend wrestle lend business cave hotdog market recording prestige short circuit unfair shop good grandmother college death difficulty route marsh"
    },
    "total_post_per_week": {
        "week_8": 37,
        "week_7": 38
    },
    "avg_post_per_user_per_month": {
        "user_17": 7,
        "user_11": 8
    }
}
```

### Technical Description and arguments on the design concept

> Dependency and Library used

```
"guzzlehttp/guzzle": "^7.3",
"nesbot/carbon": "^2.46"
```

### Design of the application

> Directory Structure
```
📦supermetrics
┣ 📂.github
┣ 📂.idea
┣ 📂 supermtric-sdk
┃    ┣ 📜 Authenticate.php
┃    ┣ 📜 SocialAnalytics.php
┣ 📜.gitignore
┣ 📜 composer.json
┣ 📜 consumer.php
┣ 📜 reamde.md
┗ 📜 supermetric_credentials.php
```

`consumer.php` is main file and supermetric-sdk is written as a library which 
anyone can include in their project and use these stats methods to get stats

1. `Authenticate.php` as it says handles the methods to authenticate using the
provided credentials and get the SL Token  for further requests
   
2. `SocialAnalytics.php` contains two methods. One method to get al the posts
and second method to basically calculate the stats from those post.
   >a. As in the task document it provided maximum page can be 10.
   > 
   >b. Assumed that while calculating stats there is no importance of the `year`
   as it was not mentioned anywhere

### Dependency and their usage reason

>`guzzlehttp/guzzle`: This library provides a lot of functionality for doing http request
> One can directly use the CURL provided by PHP but that wouyld be re-inventing the wheel.
> It also provides pool and concurrent request options which I have utilized to improve the performance

> `nesbot/carbon"`: This library provides a lot of functionality and features related to 
> date which I didn't require. But the small thing which I required to do with date, I have used carbon for that
