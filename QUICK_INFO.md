Aplikace je napsaná v PHP, Javascriptu a AJAXu s využitím Google Maps API a Google Charts API.

Aplikace je aktuálně dostupná na adrese http://filiphamsik.cz/svoz (TODO). Přihlašovací údaje jsou e-mail aa@mail.com a heslo aaa (administrátorský účet pro moje testování, dostupná i administrace), resp. test@mail.com s heslem test9876 (uživatelský účet s "reprezentativními" daty).

Po přihlášení se zobrazuje seznam dostupných modulů (např. Česká republika). Každý se skládá z několika nadscénářů (v kódu upscenario) podle skládkovacích poplatků a každý nadscénář zahrnuje několik samostatných scénářů, které představují náhodně generovaná množství ze zadaného rozsahu.

U nadcénářů a scénářů lze zobrazit použité druhy převážených odpadů (např. SKO_silnice) a lze filtrovat jejich zobrazení na mapě. Taky se může zadat hranice (např. 0,6) pro zvýraznění rizikových hran červeně podle histogramu (u nadscénářů).

Dále je možné u nadcénářů a scénářů klikat na hrany a uzly v mapě a zobrazovat informace o nich.

Vše potřebné je ke stažení tady: http://filiphamsik.cz/svoz/export/export.zip (TODO)
```
app/ - kód aplikace
data/ - data pro nahrávání
db/ - aktuální databáze s nahranými daty
doc/ - nějaké pdf - moje diplomka a dokumentace databáze a vstupních souborů
```

V administraci je pak možná správa jednotlivých modulů, uživatelských oprávnění a nahrávání dat. Nahrávání je potřeba trochu dodělat, protože neumí zpracovávat hodně velké soubory - na wedosu myslím procházely soubory do 100 000 řádků, pak docházela paměť, proto jsem je rozděloval do více menších souborů a nahrával to postupně - viz zakomentovávání částí upload.php. Tohle je potřeba nějak optimalizovat - buď nějak předzpracovávat vstupní soubory, upravit samotné zpracovávání apod. Výstupy z GAMSu se po nahrání převáděly do JSONu a pak teprve docházelo ke zpracování a uložení do databáze.

U souborů z CDV s vlastnostmi jednotlivých tras si vybavuju, že jsem prováděl nějaké šílené mapování ID uzlů z jednoho seznamu na druhý - odpad_obce.xlsx.