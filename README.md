# A warning
This project is still being built and its also being built from someone who is learning PHP, so I wouldn't rely on this for anything! 😫 Feel free to contribute and tell me what I did wrong however! 😉

# Domain-Monitor

Domain Monitor is a small project, which allows you to set up domains, it can then send you Email Notifications when a domain is about to expire.

## How to use
1. Rename .env.example to .env
2. Enter SMTP information into the .env
3. Make sure to define the storage path to somewhere or it make break a little
4. Rename config.example.php to config.php
5. Change the array in the config.php 

You can specify the URL and days to alert, and change the emails to alert, also you can change the checks, by default it checks domain expiry and SSl expiry, but you can remove one of these from the array to stop it checking.

```
	[
		'https://youtube.co.uk' => [
			'alert' => [
				'days' => 30, # Days To Alert
				'emails' => [
					'example@example.com',
				],
			],
			'checks' => [
				'domainexpiry',
				'ssl'
			]
		],

		'https://google.co.uk' => [
			'alert' => [
				'days' => 30, # Days
				'emails' => [
					'example@example.com',
				],
			],
			'checks' => [
				'ssl',
			]
		],

	];
```

5. Set up a cron tab to run, everytime it runs if the condition is met it will send an email, I'd reccomend setting a crontab to run every month.

Example to run it once every Sunday:
```
0 0 * * SUN php cron.php >/dev/null 2>&1
```
# Limitations
At the moment it only supports one level of domain and not subdomains. Though I do plan to add subdomain support!

