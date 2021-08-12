# Akeneo ETL

Akeneo ETL is a tool to manipulate product data in the Akeneo PIM using its REST API.

It is like Akeneo Enrichment Rules but with more features like expressions.

## When to use

:star: **Data cleansing**. Examples: 
* trim attribute values
* remove html tags
* generate slugs
* lowercase or uppercase

:star: **Attribute type changes**. Examples:
* copy values of a non-localisable attribute to a localisable one
* copy number data to a metric attribute

:star: **Data model changes**. Examples:
* add an attribute to all families

:star: **Migration** e.g. 
* copy a product range from UAT to production  

## How to use

Step 1. Create a connection in Akeneo or use one of existing connections.

Step 2. Create a connection profile.

Create a file connection.yaml and configure it using your connection credentials.

```yaml
host: '{{ Your Akeneo server url }}'
clientId: '{{ Client ID}}'
clientSecret: '{{ Secret }}'
userName: ' {{ Username }}'
userPassword: '{{ Password }}'
```

Step 3. Generate an ETL profile (specification of how to manipulate your data).

Create a file `etl.yaml`, e.g. this configuration allows to trim values of the `name` attribute for the `en_GB` locale:

```yaml
transform:
    actions:
        -
            type: set
            field: name
            locale: en_GB
            scope: null
            expression: 'trim(value("name", null, "en_GB", ""))'
```
See [How to configure data manipulation (ETL)](docs/configure-etl.md)

Step 4. Run the script:
```bash
bin/akeneo-etl transform --resource-type=product --connection-profile=connection.yaml --etl-profile=etl.yaml
```
