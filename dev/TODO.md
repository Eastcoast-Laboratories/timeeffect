# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.
 - invoice management: users can generate invoices for customers/projects in PDF format.

# TODO
1. dev/BULK_EDIT_EFFORTS.md

2. in der edit ansicht der efforts, die auswahlbuttons oben für die letzten 6 efforts sollen auch den tarif im js speichern, so dass der zuletzt ausgewählte tarif gleich mit eingestellt wird.

3. @setup.sh#L34-35 dies und wahrscheinlich noch einiges muss angepasst werden, seit der umstellung auf die .env als main config (siehe git history) , auch einneige files in docs/ noch

4. index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend

5. statistic/customer.php?list=1&cid=&pid=
 - die einzelnen efforts sollen verlinkt werden, so dass man die details sehen kann
 - in jedem effort in der spalte "kosten" die kosten angeben in z.b. EURO (je nach einstellung), falls unassigned oder noch kein tarif ausgewählt ist fr den effort, dann "kein Tarif" angeben
 - der trennstrich über jedem projekt doppelte dicke
 - bevor ein zeile mit einem neuen projekt oder kunden beginnt eine leerzeile einfügen mit den summierten beträgen des darüberliegenden projekts in der spalte Kosten

 