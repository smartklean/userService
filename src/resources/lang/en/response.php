<?php

return [
  /*
  \------------------------------------------------------------------
  \ Response Language Lines
  \------------------------------------------------------------------
  \
  \ The following language lines are used when sending responses from
  \ various endpoints. You are free to modify these language lines
  \ according to your application's requirements.
  */

  'errors' => [
    'server' => 'Internal Server Error.',
    'request' => 'Bad Request.',
    'unauthenticated' => 'Unauthenticated.',
    'not_found' => 'Resource Not Found'
  ],

  'codes' => [
    'success' => '00',
    'error' => '01',
    'validation_error' => '02',
    'not_found_error' => '03',
    'unauthenticated' => '05',
  ],

  'messages' => [
    'added' => 'The :attr was added successfully.',
    'added_multiple' => 'The :attr were added successfully.',
    'not_added' => 'Something went wrong trying to add the :attr.',
    'updated' => 'The :attr was updated successfully.',
    'updated_multiple' => 'The :attr were updated successfully.',
    'not_updated' => 'Something went wrong trying to update the :attr.',
    'uploaded' => 'The :attr was uploaded successfully.',
    'uploaded_mulitple' => 'The attr: were uploaded successfully.',
    'not_uploaded' => 'Something went wrong trying to upload the :attr.',
    'found' => 'The :attr was retrived successfully.',
    'found_multiple' => 'The :attr were retrived successfully.',
    'not_found' => 'The :attr was not found.',
    'not_found_multiple' => 'The :attr were not found.',
    'available' => 'The :attr is available.',
    'not_available' => 'The :attr is not available.',
    'deleted' => 'The :attr was deleted successfully.',
    'deleted_multiple' => 'The :attr were deleted successfully.',
    'verified' => 'The :attr was verified successfully.',
    'verification_revoked' => 'The :attr is no longer verified.',
    'not_verified' => 'The :attr could not be verified.',
    'authenticated' => 'These user was authenticated successfully.',
    'unauthenticated' => 'These credentials do not match our records.',
    'verification_code_sent' => 'The verification code was sent successfully',
    'password_email' => 'We\'ve emailed you your password reset token.',
    'password_reset' => 'Your password was reset successfully.',
    'token_reset' => 'The token was reset successfully.',
    'token_invalid' => 'The token provided is invalid.',
    'token_revoked' => 'The tokens for this user were revoked successfully.',
    'validation' => 'One or more parameters did not pass the validation checks.',
  ]
];
