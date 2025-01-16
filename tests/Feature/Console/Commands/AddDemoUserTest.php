<?php

use function Pest\Laravel\assertDatabaseHas;

uses()->group('backend');

it('adds a new demo user', function () {
    $this->artisan('app:add-demo-user')
        ->expectsOutputToContain('Adding Dr. Dolitle to users');

    assertDatabaseHas('users', ['username' => 'drdoli']);
});

it('adds demo user presenter ', function () {
    $this->artisan('app:add-demo-user');

    assertDatabaseHas('presenters', ['username' => 'drdoli']);
});

it('adds demo user series ', function () {
    $this->artisan('app:add-demo-user')
        ->expectsOutputToContain('Adding Dr. Dolittle series');

    assertDatabaseHas('series', ['title' => 'The story of Dr. Dollitle']);
});

it('adds demo user clips to series ', function () {
    $this->artisan('app:add-demo-user')
        ->expectsOutputToContain('Adding Dr. Dolittle clips to his series');

    assertDatabaseHas('clips', ['title' => 'Taby cat']);
    assertDatabaseHas('clips', ['title' => 'Goat']);
});

it('adds demo assets to user series', function () {
    $this->artisan('app:add-demo-user')
        ->expectsOutputToContain('Adding Dr. Dolittle clips to his series');
});
