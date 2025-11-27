#!/bin/bash

# Start CUPS in background
/usr/sbin/cupsd -f &
CUPS_PID=$!

# Wait for CUPS to start
sleep 3

# Add a virtual PDF printer for testing
lpadmin -p TestPrinter -E -v cups-pdf:/ -m lsb/usr/cups-pdf/CUPS-PDF_opt.ppd -o printer-is-shared=true
cupsenable TestPrinter
cupsaccept TestPrinter

# Set as default printer
lpadmin -d TestPrinter

echo "CUPS server ready with TestPrinter"

# Keep container running
wait $CUPS_PID

