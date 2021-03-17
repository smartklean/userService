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
  ],

  'messages' => [
    'users' => [
      'added' => 'The user was added successfully.',
      'added_multiple' => 'The users were added successfully.',
      'updated' => 'The user was updated successfully.',
      'updated_multiple' => 'The users were updated successfully.',
      'found' => 'The user was retrived successfully.',
      'found_multiple' => 'The users were retrived successfully.',
      'not_found' => 'The user could not be found.',
      'deleted' => 'The user was deleted successfully.',
      'deleted_multiple' => 'The users were deleted successfully.',
      'authenticated' => 'These user was authenticated successfully.',
      'unauthenticated' => 'These credentials do not match our records.',
      'token_revoked' => 'The tokens for this user were revoked successfully.',
    ],

    'validation' => 'One or more parameters did not pass the validation checks.',
  ]
];
