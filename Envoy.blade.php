@setup
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

try {
$dotenv->load();
$dotenv->required(['DEPLOY_USER', 'DEPLOY_GROUP','DEPLOY_SERVER', 'DEPLOY_BASE_DIR', 'DEPLOY_REPO'])->notEmpty();
} catch ( Exception $e )  {
echo $e->getMessage();
}

$user = env('DEPLOY_USER');
$group = env('DEPLOY_GROUP');
$repo = env('DEPLOY_REPO');
$branch = env('DEPLOY_BRANCH');

if (!isset($baseDir)) {
$baseDir = env('DEPLOY_BASE_DIR');
}

if (!isset($branch)) {
$branch = 'main';
}

$releaseDir = $baseDir . '/releases';
$currentDir = $baseDir . '/current';

date_default_timezone_set('Europe/Berlin');
$release = date('YmdHis');
$currentReleaseDir = $releaseDir . '/' . $release;

function logMessage($message) {
return "echo '\033[32m" .$message. "\033[0m';\n";
}
@endsetup

@servers(['development' => env('DEPLOY_USER').'@'.env('DEPLOY_SERVER')])

@story('deploy-on-dev',['on'=>'development'])
git-develop
composer
npm_install
npm_run_prod
update_symlinks
clear-optimizer
rebuild-cache
fix-broken-links
clean_old_releases
{{--set_permissions--}}
@endstory

@task('git-develop')
{{ logMessage("Cloning repository in {$branch} branch") }}

git clone {{ $repo }} --branch={{ $branch }} --depth=1 -q {{ $currentReleaseDir }}
@endtask

@task('composer')
{{ logMessage("Running composer") }}

cd {{ $currentReleaseDir }}

/usr/local/bin/composer install --quiet --no-interaction --no-dev --prefer-dist --optimize-autoloader --ignore-platform-reqs
@endtask

@task('npm_install')
{{ logMessage("NPM install") }}

cd {{ $currentReleaseDir }}

npm install --silent --no-progress > /dev/null
@endtask

@task('npm_run_prod')
{{ logMessage("NPM run prod") }}

cd {{ $currentReleaseDir }}

npm run build --silent --no-progress > /dev/null

{{ logMessage("Deleting node_modules folder") }}
rm -rf node_modules
@endtask

@task('update_symlinks')
{{ logMessage("Updating symlinks") }}

# Remove the storage directory and replace with persistent data
{{ logMessage("Linking storage directory") }}
rm -rf {{ $currentReleaseDir }}/storage;
cd {{ $currentReleaseDir }};
ln -nfs {{ $baseDir }}/storage {{ $currentReleaseDir }}/storage;
ln -nfs {{ $baseDir }}/storage/app/public {{ $currentReleaseDir }}/public/storage

# Remove the public uploads directory and replace with persistent data
#    {{ logMessage("Linking uploads directory") }}
#    rm -rf {{ $currentReleaseDir }}/public/uploads
#    cd {{ $currentReleaseDir }}/public
#    ln -nfs {{ $baseDir }}/uploads {{ $currentReleaseDir }}/uploads;

# Import the environment config
{{ logMessage("Linking .env file") }}
cd {{ $currentReleaseDir }};
ln -nfs {{ $baseDir }}/.env .env;

# Symlink the latest release to the current directory
{{ logMessage("Linking current release") }}
ln -nfs {{ $currentReleaseDir }} {{ $currentDir }};
@endtask

@task('set_permissions')
# Set dir permissions
{{ logMessage("Set permissions") }}

sudo chown -R {{ $user }}:{{ $group }} {{ $baseDir }}
sudo chmod -R ug+rwx {{ $baseDir }}/storage
cd {{ $baseDir }}
sudo chown -R {{ $user }}:{{ $group }} current
sudo chmod -R ug+rwx current/storage current/bootstrap/cache
sudo chown -R {{ $user }}:{{ $group }} {{ $currentReleaseDir }}
@endtask

@task('reload_services', ['on' => 'prod'])
# Reload Services
{{ logMessage("Restarting service supervisor") }}
sudo supervisorctl restart all

{{ logMessage("Reloading php") }}
sudo systemctl reload php7.3-fpm
@endtask

@story('clear-cache')
clear-optimizer
rebuild-cache
@endstory

@task('clear-optimizer',['on'=>'development'])
{{ logMessage("Clearing optimizer") }}
php {{ $currentDir }}/artisan optimize:clear
@endtask

@task('rebuild-cache',['on'=>'development'])
{{ logMessage("Starting rebuilding cache...") }}
php {{ $currentDir }}/artisan view:cache

php {{ $currentDir }}/artisan route:cache

php {{ $currentDir }}/artisan config:cache

php {{ $currentDir }}/artisan icons:cache
@endtask

@task('fix-broken-links',['on'=>'development'])
cd {{ $currentReleaseDir }};
rm {{ $currentDir }}/public/links
rm {{ $currentDir }}/public/documents
php {{ $currentDir }}/artisan storage:link
@endtask


@task('clean_old_releases')
# Delete all but the 5 most recent releases
{{ logMessage("Cleaning old releases") }}
cd {{ $releaseDir }}
ls -dt {{ $releaseDir }}/* | tail -n +6 | xargs -d "\n" rm -rf;
@endtask

@finished
echo "Envoy deployment script finished.\r\n";
@endfinished
