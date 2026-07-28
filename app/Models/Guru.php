<?php

namespace App\Models;

/**
 * Backward-compatible alias for GuruTartil.
 * All guru references in existing code point to tartil teachers.
 */
class Guru extends GuruTartil
{
    // This class extends GuruTartil for backward compatibility.
    // New code should use GuruTartil directly.
    // GuruReguler is a separate model for regular class teachers.
}
