# BiSight

## Installation

### Create database schema

1. Creata a valid `bisight.conf` file for the database-manager
2. Run the following command:
```
/vendor/bin/database-manager database:loadschema bisight app/schema.xml --apply
```
3. Add one or more records to the `bisight.user` table to login.

### Starting the service:

    php -S 0.0.0.0:8080 -t web/
