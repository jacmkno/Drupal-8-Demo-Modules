# Demo Modules For Drupal 8
The modules work together to demonstrate differente features. 

## What do the modules do?

The modules create a set of URLs in your drupal installation where you can test form posting, ajax callas, rest endpoints, form validation, schema creation, and other features.

### Demo site:
- Form: https://drupal8demo.activisual.net/example-module/form
- Example Page: https://drupal8demo.activisual.net/example-module/data
- CRUD Rest Endpoint: https://drupal8demo.activisual.net/example-crud/data

### RESTful Resource:
- `/example-crud/data` works by using the restuful PATCH, POST, DELETE and GET methods and receives **raw JSON** payloads with the record descriptions.
- `GET /example-crud/data`: List all records created in `/example-module/form`
- `POST /example-crud/data`: Adds new records
- `PATCH /example-crud/data/{id}`: Updates a record
- `DELETE /example-crud/data/{id}`: Removes a record

### RESTful fetch call: 
You can copy and paste this in your browser console to add a new record, just update the domain in the path to match your deployment site:
```
fetch('/example-crud/data',{'method':'POST',
  body:JSON.stringify({
    "name":"yyyyyyy",
    "idNum":"123123123",
    "dob":null,
    "role":"admin"
  }), 
  headers: { 'Content-Type': 'application/json' }
});
```

## Download Links
- **Crud Module:** https://github.com/jacmkno/Drupa-8-Demo-Modules/raw/main/crud.tar.gz
- **Form Module:** https://github.com/jacmkno/Drupa-8-Demo-Modules/raw/main/demo.tar.gz

## Install instructions:
Upload the files to your drupal installation through the module installation interface or decompress them in your /modules folder/
