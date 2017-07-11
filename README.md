# SIMA
Scan Inventory for Mail Attachments

# Disclaimer

This tool is still under development, so you might missing or looking at incomplete commands.
If you find any bugs, please report them.

## Installation

- Check out this repository to /opt/sima/
- Create database, user and schema:

```bash
mysql -e "CREATE DATABASE sima;"
mysql -e "CREATE USER 'sima'@'localhost' IDENTIFIED BY 'some_secret_password';"
mysql -e "GRANT ALL PRIVILEGES ON sima.* TO 'sima'@'localhost';"
mysql -u sima -psome_secret_password -d sima < /opt/sima/database/schema.sql
```

- Configure application:

```bash
vi /opt/sima/config/database.yml
vi /opt/sima/config/mail.yml
vi /opt/sima/config/avtotal.yml
vi /opt/sima/config/base.yml
```

- Configure Amavis:

For Ubuntu, edit /etc/amavis/conf.d/15-av_scanners and add:

```bash
@av_scanners = (
  ### SIMA / Scan Inventory for Mail Attachments
  ['SIMA', ['/opt/sima/bin/sima'],
    'scan {}', [0], qr/Found bad hash/m, qr/Found bad hash (.+) in/m ],
)
```

This will ONLY start to collect information. If you change the 'scan {}' into 'scan -f {}'
the filter policy will be applied. Do not enable filtering unless you have testing correct
working of the system FIRST.


## Stuff to do

- Create central API to exchange hash information (replace AVTotal API)
- Exchange hashes with mail vendors to build a large database
- Create filtering and policy builder

