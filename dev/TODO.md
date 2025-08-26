# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.
 - invoice management: users can generate invoices for customers/projects in PDF format.

# TODO
1. dev/BULK_EDIT_EFFORTS.md

2. in der edit ansicht der efforts, die auswahlbuttons oben für die letzten 6 efforts sollen auch den tarif im js speichern, so dass der zuletzt ausgewählte tarif gleich mit eingestellt wird.

3. @setup.sh#L34-35 dies und wahrscheinlich noch einiges muss angepasst werden, seit der umstellung auf die .env als main config (siehe git history) , auch einneige files in docs/ noch

4. index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend

5. mach mal einen md plan in ndev/ wie man rechnungen autmatisierrt erzeugen kann. ich habe einen kunden für den ich jeden monat fest 1500 erruo vereinbart habe für 15h. wenn ich mehr oder weniger arbeite, dann muss ich immer einen übertrag mitführen.

wahrscheinlich kann man das am besten einbauen, wenn man den report erstellt und die haken "berechnen"  sezt unddann auf "berechne" buton drückt, da müsste dann automatisch die rechnung geeriert werden und abgespeichert unter iener fortlaufenden nummer. diese rechnungen müsste man auch wieder abrufen können oder noch mal bearbeiten im detail ausserdem müsste dort die mwst eausgewiesen werden und eine beschriftung für die stunden festgelegt werden, also ich will nicht in der rechnung alle einzelnen efforts aufgelistet haben sondern unur die gesamtsumme , die efforts sollen in eiem anhang stehen und dort soll auch der berhang berechnet werden und aufgelistet (optional)