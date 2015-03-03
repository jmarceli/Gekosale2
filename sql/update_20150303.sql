-- translation for TXT_COOKIE_POLICY_URL
INSERT INTO translation (name)
  SELECT 'ERR_DELIVERY_SELECT_TITLE'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'ERR_DELIVERY_SELECT_TITLE')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT 'Nie wybrano sposobu dostawy', idtranslation, 1
  FROM translation
  WHERE name = 'ERR_DELIVERY_SELECT_TITLE'
    ON DUPLICATE KEY UPDATE translation = 'Nie wybrano sposobu dostawy';
-- translation for TXT_COOKIE_SECOND
INSERT INTO translation (name)
  SELECT 'ERR_DELIVERY_SELECT_DESC'
  FROM translation
  WHERE NOT EXISTS (SELECT * FROM translation WHERE name = 'ERR_DELIVERY_SELECT_DESC')
  LIMIT 1;
INSERT INTO translationdata (translation, translationid, languageid)
  SELECT 'Prosimy o wybór sposobu dostawy w celu złożenia zamówienia.', idtranslation, 1
  FROM translation
  WHERE name = 'ERR_DELIVERY_SELECT_DESC'
    ON DUPLICATE KEY UPDATE translation = 'Prosimy o wybór sposobu dostawy w celu złożenia zamówienia.';

