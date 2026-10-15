<?php

namespace App\Exceptions;

use DomainException;

/**
 * A lead cannot be validated or rejected in its current state. The message
 * is user-facing and shown by both the API (422) and the admin panel.
 */
class LeadValidationException extends DomainException {}
