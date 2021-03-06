# TodoMVC like, made with Laravel - Tailwindcss - Livewire - Alpinejs

## Screenshot

![TodoList](/screenshots/screenshot_app.png)

## Details

Inspired by [TodoMVC](http://todomvc.com/).

I made this Todo List app to try the combo Laravel, Livewire, Alpinejs and Tailwindcss.
You can check this repo to see how things are made, a lot of Livewire features are used, and all of this was made with TDD.

## Tech

-   Laravel 7.3.0
-   Livewire 1.0.10
-   Alpinejs 2.2.1
-   Tailwindcss 1.2.0

## Usage

First clone this repository on your local machine : <br>
`git clone https://github.com/YannickYayo/livewire-todolist.git` <br>

Then install the dependencies and compile the assets : <br>

```bash
cd livewire-todolist

composer install && npm install && npm run dev
```

Now you need to copy your file `.env.example` to `.env` and update your database credentials :

```
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

Don't forget to create your database first. <br>

When the database configuration is done, run this command to generate an application key :

```bash
php artisan key:generate
```

Run migrations and seeds :

```bash
php artisan migrate --seed
```

And finally launch your server :

```bash
php artisan serve
```

## Usefull commands

-   Run Phpunit : `composer test`
-   Run Larastan : `composer analyse`
-   Run PHP-Cs-Fixer : `composer format`
-   Run Prettier : `npm run format`
-   Run Eslint : `npm run lint`

## @TODO

-   Update items per page
-   Fix search property not updated on query string
-   Find a way to validate an edited todo, Livewire's validation seems to not fit for this case
