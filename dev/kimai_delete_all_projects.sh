#!/bin/bash

##############################################################################
# Kimai Delete All Projects Script
# 
# This script deletes all projects and connected data from Kimai Docker.
# WARNING: This is a destructive operation and cannot be undone!
#
# Usage: ./kimai_delete_all_projects.sh
##############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# Confirmation prompt
log_warn "WARNING: This will DELETE all projects and connected data from Kimai!"
log_warn "This operation CANNOT be undone!"
echo ""
read -p "Are you sure you want to continue? Type 'yes' to confirm: " confirmation

if [ "$confirmation" != "yes" ]; then
    log_info "Operation cancelled"
    exit 0
fi

log_info "Deleting all projects and connected data from Kimai..."

# Delete data from database
sudo docker exec kimai_db mysql -u kimai -pkimai_password kimai_db -e "
DELETE FROM kimai2_timesheet;
DELETE FROM kimai2_projects;
DELETE FROM kimai2_activities;
DELETE FROM kimai2_invoices;
DELETE FROM kimai2_timesheet_meta;
DELETE FROM kimai2_timesheet_tags;
DELETE FROM kimai2_projects_meta;
DELETE FROM kimai2_activities_meta;
DELETE FROM kimai2_activities_rates;
DELETE FROM kimai2_projects_rates;
DELETE FROM kimai2_projects_comments;
DELETE FROM kimai2_projects_teams;
DELETE FROM kimai2_activities_teams;
DELETE FROM kimai2_invoices_meta;
"

log_info "All projects and connected data deleted successfully"
log_info "Kimai is ready for fresh data import"
