<?php

use App\Enums\Acl;
use App\Enums\Role;
use Facades\Tests\Setup\ClipFactory;

use function Pest\Laravel\get;

uses()->group('frontend');

beforeEach(function () {
    $this->clip = ClipFactory::withAssets(2)->create(['password' => '1234qwER']);
});

it('a clip with a portal acl can be only be accessable for logged in users', function () {
    $this->clip->addAcls(collect([Acl::PORTAL()]));
    get(route('frontend.clips.show', $this->clip))->assertDontSee('mediaPlayer id="target"', false);

    signIn();
    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});

it('a clip with lms acl can be only accessable for lms users', function () {
    $this->clip->addAcls(collect([Acl::LMS()]));
    $client = getUrlClientType(Acl::LMS->lower());
    get(route('frontend.clips.show', $this->clip))->assertDontSee('mediaPlayer id="target"', false);

    $time = dechex(time());
    $token = md5('clip'.'1'.$this->clip->password.'0.0.0.0'.$time.$client);
    $link = '/protector/link/clip/1/'.$token.'/'.$time.'/'.$client;
    get($link)->assertForbidden();

    $token = md5('clip'.'1'.$this->clip->password.'127.0.0.1'.$time.$client);
    $link = '/protector/link/clip/1/'.$token.'/'.$time.'/'.$client;
    get($link)->assertStatus(302);
    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});

it('a clip with lms acl can be accessable for clip admin', function () {
    $this->clip->addAcls(collect([Acl::LMS()]));
    get(route('frontend.clips.show', $this->clip))->assertDontSee('mediaPlayer id="target"', false);

    signIn($this->clip->owner);
    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});

it('a clip with lms acl can be accessable for portal admin ', function () {
    $this->clip->addAcls(collect([Acl::LMS()]));
    get(route('frontend.clips.show', $this->clip))->assertDontSee('mediaPlayer id="target"', false);

    signInRole(Role::ADMIN);
    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});

it('a clip with lms acl can be accessable for portal superadmin ', function () {
    $this->clip->addAcls(collect([Acl::LMS()]));
    get(route('frontend.clips.show', $this->clip))->assertDontSee('mediaPlayer id="target"', false);

    signInRole(Role::SUPERADMIN);
    get(route('frontend.clips.show', $this->clip))->assertSee('mediaPlayer id="target"', false);
});
