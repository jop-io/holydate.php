# holydate.php
PHP-klass för att kontrollera och benämna datum som högtids- eller helgdagar

# TODO
Uppdatera denna sida med värd information och bra exempel...

# Exempel på användning
```php
$hd = new Holydate();

// Registrera datum
$hd->Register('Påskafton')->Easter()->Offset(-1);
$hd->Register('Julafton')->Date()->Month(12)->Day(24);
$hd->Register('Juldagen')->Date()->Month(12)->Day(25);
$hd->Register('Midsommarafton')->Weekday(5)->Month(6)->Day(19)->IsRed();
$hd->Register('Mors dag')->Weekday(7)->Month(5)->Day(1)->Last();
$hd->Register('Fars dag')->Weekday(7)->Month(11)->Day(1)->Interval(2);

// Kontrollera datum
print $hd->Check('2017-04-15'); // Påskafton
print $hd->Check('2016-12-24'); // Julafton
print $hd->Check('2016-12-25'); // Julafton
print $hd->Check('2017-06-23'); // Midsommarafton
print $hd->Check('2017-05-28'); // Mors dag
print $hd->Check('2017-11-12'); // Fars dag
```
