# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.
 - invoice management: users can generate invoices for customers/projects in PDF format.

# TODO
1. dev/BULK_EDIT_EFFORTS.md

2. in der edit ansicht der efforts, die auswahlbuttons oben für die letzten 6 efforts sollen auch den tarif im js speichern, so dass der zuletzt ausgewählte tarif gleich mit eingestellt wird.

3. @setup.sh#L34-35 dies und wahrscheinlich noch einiges muss angepasst werden, seit der umstellung auf die .env als main config (siehe git history) , auch einneige files in docs/ noch

4. index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend

5.


Fatal error: Uncaught Error: Call to undefined method DB_Sql::prepare() in /var/www/html/include/contract.class.php:192 Stack trace: #0 /var/www/html/inventory/contracts.php(62): Contract->hasOverlappingContract('5', NULL, '2025-08-28', NULL, NULL) #1 {main} thrown in /var/www/html/include/contract.class.php on line 192

@contract.class.php#L192-194 benutze hier die selbe alte  db zugang wie sonst in timeeffect berall