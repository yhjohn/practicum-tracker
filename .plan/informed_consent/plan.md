# Files
Please refer to `./public_consent.html` all the time so you know what you doing

# Description
This acts as a client approval form, and this should be tied to the app\Models\Client model. as it needs to belong to the client model.

The idea is that this will become a public url that is un-authed but it should have a short lived token string in the URL.

Please refer to app/models/Intake and app/FIlament/Schema/Intakes/IntakeWizard.php for some inspiration.

Also in the ViewClient.php please add a link to generate the consent.

# Approach

1. Create model name Consent, and create migration that match each form fields in the html
2. Run Migration
3. Create the form/info