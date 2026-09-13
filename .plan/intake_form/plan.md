# Files
Please refer to `./intake.html` all the time so you know what you doing

# Description
This acts as a info gathering form, and this should be tied to the app\Models\Client model. as it needs to belong to the client model. Now when create the Client Model I would like some part of these to be automatically filled.

The idea is that this will become a public url that is un-authed but it should have a short lived token string in the URL.

# Approach

1. Create model name Intake, and create migration that match each form fields in the html
2. Run Migration
3. Create wizard steps to correspond to the html and insert the form field using FIlament PHP.