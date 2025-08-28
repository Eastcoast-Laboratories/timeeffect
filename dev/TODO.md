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
jetzt so, jetzt schau dir funktionierende dateien an, wie z.b. @form.ihtml.php#L120 
mache alles genau so wie dort in den neuen dateien seit dem commit

und dann :
@copilot-instructions.md#L1-295 ergänze hier, wie man die lokalisierungsdateien richtig einbindet


benutze möglichst besteehende lokalize strings (z.b. "Customer" ist ja schon ) und  lokalisiere alle neuen dateien seit commit 0b3fe983

hre nicht auf, bis nidcht alle dateien lokalisiert sind, wen nötig, mache in dev/ ein log file, in dem du abhakst, welche du fertig klokalisiert hast komplett
