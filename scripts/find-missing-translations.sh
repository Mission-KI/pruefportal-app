#!/bin/bash

# Find entries in de/default.po that have empty translations
# Filters out the header and handles multiline entries correctly

awk '
BEGIN {
    entry = ""
    has_msgid = 0
    in_msgstr = 0
    msgstr_has_content = 0
}
/^#:/ {
    # Save previous entry if msgstr was empty
    if (has_msgid && !msgstr_has_content) {
        print entry
    }
    # Start new entry
    entry = $0 "\n"
    has_msgid = 0
    in_msgstr = 0
    msgstr_has_content = 0
    next
}
/^msgid/ {
    entry = entry $0 "\n"
    has_msgid = 1
    in_msgstr = 0
    next
}
/^msgstr ""$/ {
    entry = entry $0 "\n"
    in_msgstr = 1
    next
}
/^msgstr ".+"/ {
    # msgstr with content on same line
    entry = ""
    has_msgid = 0
    in_msgstr = 0
    msgstr_has_content = 1
    next
}
/^"/ {
    if (in_msgstr) {
        # Continuation line after msgstr "" - means it has content
        msgstr_has_content = 1
    }
    entry = entry $0 "\n"
    next
}
END {
    # Check last entry
    if (has_msgid && !msgstr_has_content) {
        print entry
    }
}
' resources/locales/de/default.po > resources/locales/NEW_TRANSLATIONS.txt

echo "Missing translations written to: resources/locales/NEW_TRANSLATIONS.txt"
