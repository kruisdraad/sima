# sima
Scan Inventory for Mail Attachments


## Installation

- Check out this repository to /opt/sima/
- Create database, user and schema:

```bash
mysql -e "CREATE DATABASE sima;"
mysql -e "CREATE USER 'sima'@'localhost' IDENTIFID BY 'some_secret_password';"
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
  ### MFD / Malware Filter Database
  ['Malware Filter Database', ['/opt/sima/bin/scan'],
    '{}', [0], qr/Found bad hash/m, qr/Found bad hash (.+) in/m ],
)
```
