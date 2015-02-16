-- translation for TXT_COOKIE_FIRST
INSERT INTO translation (name)
  SELECT 'TXT_COOKIE_FIRST'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'TXT_COOKIE_FIRST')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT 'Strona korzysta z plików cookie w celu realizacji usług zgodnie z', idtranslation, 1
  FROM translation
  WHERE name = 'TXT_COOKIE_FIRST'
    ON DUPLICATE KEY UPDATE translation = 'Strona korzysta z plików cookie w celu realizacji usług zgodnie z';
-- translation for TXT_COOKIE_POLICY
INSERT INTO translation (name)
  SELECT 'TXT_COOKIE_POLICY'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'TXT_COOKIE_POLICY')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT 'Polityką prywatności', idtranslation, 1
  FROM translation
  WHERE name = 'TXT_COOKIE_POLICY'
    ON DUPLICATE KEY UPDATE translation = 'Polityką prywatności';
-- translation for TXT_COOKIE_POLICY_URL
INSERT INTO translation (name)
  SELECT 'TXT_COOKIE_POLICY_URL'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'TXT_COOKIE_POLICY_URL')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT '/polityka-prywatnosci', idtranslation, 1
  FROM translation
  WHERE name = 'TXT_COOKIE_POLICY_URL'
    ON DUPLICATE KEY UPDATE translation = '/polityka-prywatnosci';
-- translation for TXT_COOKIE_SECOND
INSERT INTO translation (name)
  SELECT 'TXT_COOKIE_SECOND'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'TXT_COOKIE_SECOND')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT 'Możesz określić warunki przechowywania lub dostępu do cookie w Twojej przeglądarce lub konfiguracji usługi.', idtranslation, 1
  FROM translation
  WHERE name = 'TXT_COOKIE_SECOND'
    ON DUPLICATE KEY UPDATE translation = 'Możesz określić warunki przechowywania lub dostępu do cookie w Twojej przeglądarce lub konfiguracji usługi.';

