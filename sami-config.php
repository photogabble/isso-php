<?php

use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;
use Sami\Sami;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude([
        '.travis',
        '.sami',
        'vendor',
        'node_modules',
    ])
    ->in(realpath(__DIR__));

$versions = GitVersionCollection::create(realpath(__DIR__))
    ->add('master', 'master branch');

return new Sami($iterator, [
    'title' => 'php-isso',
    'versions' => $versions,
    'build_dir' => __DIR__ . '/.sami/build/%version%',
    'cache_dir' => __DIR__ . '/.sami/cache/%version%',
    'insert_todos' => true,
    'remote_repository' => new GitHubRemoteRepository('photogabble/isso-php', realpath(__DIR__)),
]);