# MODX Constant Contact v3

> FormIt Hook to send contact into Constant Contact

**Create System Settings**

1. cc_refresh_token
2. cc_client_id 
3. cc_client_secret
4. cc_redirect_uri
5. cc_auth_code

*client_id = api key

## Setup Authorization

1. Setup an unpublished blank page that calls `[[!ConstantContactSetup]]`
2. Have *System Settings* page loaded and ready to paste Step 3 code
3. Client or someone with login creds needs to hit this URL and copy the code returned in the URL

`https://api.cc.email/v3/idfed?response_type=code&client_id=9f36069e-182c-484e-8c2c-15e329de85b7&scope=contact_data+campaign_data&redirect_uri=https%3A%2F%2Frocketcitydigital.com%2Fcc-auth`

4. You have 60 seconds to paste the *Auth Code* into System Settings and run the page from step 1
5. copy the refresh token from the page and paste into System Settings

All set. Test the form. 

## Form Creation

1. add your form fields to the Snippet
2. Get the List ID from the URL `https://app.constantcontact.com/pages/contacts/ui#contacts/0ce51e9e-47ee-XXXXX-XXXXXXX`
