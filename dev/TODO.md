# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.

# TODO

2. in der edit ansicht der efforts, die auswahlbuttons oben für die letzten 6 efforts sollen auch den tarif im js speichern, so dass der zuletzt ausgewählte tarif gleich mit eingestellt wird.

3. @setup.sh#L34-35 dies und wahrscheinlich noch einiges muss angepasst werden, seit der umstellung auf die .env als main config (siehe git history) , auch einneige files in docs/ noch

4. index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend

5. Invoice verbessern

6. 
bei http://localhost/inventory/efforts.php?stop_all=1

auch die beschreibungen  auflisten mit links zum bearbeiten ders efforts , welche efforts gestoppt wurden (localized)@efforts.php#L17-45 

10. in der effort detail ansicht einen löschen button einbauen

11. 
in der ansicht für tarife bearbeiten:
customer.php?edit=1&rates=1&cid=4

, soll es eine radio box geben, für den standard tarif, also eine db migration nötig, die in die vorhandene migration eingebaut werden muss. 

erstelle als erstes im dev ordner ein md file mit diesen ganzen änderungen

- wenn man beim report bei Enddatum einen tag auswählt, der in dem monat nicht existiert (z.b. 31. im september), soll er automatisch den letzten tag des monats nehmen (also den 30.sept.) anstatt die tage im folgemonat weiterzuzählen