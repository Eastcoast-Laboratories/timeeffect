# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.
 - invoice management: users can generate invoices for customers/projects in PDF format.

# TODO
1. das pdf generierte report muss die gleiche funktioinalität bekommen, wie die csv. am besten eine zentrale funktion für beide zum generieren der daten die in beiden benutzt wird und dann nur anders umgesetzt wird (DRY) 

2.
- index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend


- statistic/customer.php?list=1&cid=&pid=
 - die einzelnen efforts sollen verlinkt werden, so dass man die details sehen kann
 - in jedem effort in der spalte "kosten" die eizelne summe angeben in z.b. EURO (je nach einstellung), 
 - der trennstrich über jedem projekt doppelte dicke
 - bevor ein zeile mit einem neuen projekt oder kunden beginnt eine leerzeile einfügen mit den summierten beträgen des darüberliegenden projekts in der spalte Kosten