# Post Processor Workbench

ppwb is a set of tools used in post-processing, the final stage of ebook
production.

The workbench is a web front-end written in PHP that performs a variety
of post-processing activities by calling out to external programs.
It consists of a user-visible page (`index.php`) with links to programs for
text analysis (see `pptext.php`), HTML analysis (see `pphtml.php`),
"smart quote" processing (see `ppsmq.php`) and a file compare tool
(see `ppcomp.php`).

The following two external tools need to be installed in directories under
`bin/`:
* [pptext](https://github.com/DistributedProofreaders/pptext) in `bin/pptext/`
* [pphtml](https://github.com/DistributedProofreaders/pphtml) in `bin/pphtml/`
* [ppcomp](https://github.com/DistributedProofreaders/ppcomp) in `bin/ppcomp/`

It's recommended that you clone those repos into `bin/` directly:

```bash
cd bin
git clone https://github.com/DistributedProofreaders/pptext.git
git clone https://github.com/DistributedProofreaders/pphtml.git
git clone https://github.com/DistributedProofreaders/ppcomp.git
```

See the individual tools' README.md for their prerequisites.

For ppcomp to work, `dwdiff` needs to be installed as well.

## Python environment

The external python programs require various python libraries to be installed
in order to work. Consult with each of the tools' `requirements.txt` files
for more details. These can be installed in the system's python3 installation
(not recommended) or in a virtualenv dedicated for ppwb (preferred).

If using a virtualenv, you need to update `$python_runner` in `config.php` to
call it like this:
```php
$python_runner = "env VIRTUAL_ENV=/path/to/venv PATH=/path/to/venv/bin/python3";
```

Ensure that the web server can access the virtualenv. virtualenvs created
in `~/.local` may need to have the permissions updated (o+x) to allow the
web server sufficient permissions.

## Using Docker for development / testing

For convenience, you can run ppwb, ppcomp, pphtml, and pptext in a container.
The included Docker Compose configuration will run a PPWB instance on localhost,
using the code on your computer (outside the container) mounted into the
container.

See the above instructions about installing ppcomp, pptext, pphtml under the
"bin" directory. Depending what you're working on, you can clone the repository
URLs above, or substitute your own forked URLs.

Once they're in place, build the image:

    docker compose build

And then bring up the service:

    # add -d if you prefer to run in the background
    docker compose up

Visit http://localhost:8080 in your browser. (The port can be changed by editing
docker-compose.yml, if necessary.)

### When changes take effect

To change pptext Go code, you'll need to restart services.
(The startup script rebuilds pptext.)

    docker compose restart

If you make changes to `Dockerfile` or `docker-entrypoint.sh`, rebuild the
image and restart.

To change the port of the web server, update `docker-compose.yml` and restart.

All other changes should take effect as soon as you save the file.
