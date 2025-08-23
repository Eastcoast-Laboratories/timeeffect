# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.
 - invoice management: users can generate invoices for customers/projects in PDF format.

# TODO

- Reports index list should add links to each reports in target _blank, so you can check details before billing
- pdf report: 
Warning: Undefined variable $foot_notes in /var/www/html/templates/statistic/pdf/list.ihtml.php on line 298

Fatal error: Uncaught Exception: FPDF error: Incorrect output destination: 1 in /var/www/html/include/fpdf.inc.php:271 Stack trace: #0 /var/www/html/include/fpdf.inc.php(1028): FPDF->Error('Incorrect outpu...') #1 /var/www/html/templates/statistic/pdf/list.ihtml.php(317): FPDF->Output(true, '-project4.pdf') #2 /var/www/html/statistic/pdf.php(15): include('/var/www/html/t...') #3 {main} thrown in /var/www/html/include/fpdf.inc.php on line 271
