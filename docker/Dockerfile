FROM debian:bookworm-slim

# Install CUPS and PDF support
RUN apt-get update && apt-get install -y \
    cups \
    cups-bsd \
    cups-client \
    cups-pdf \
    printer-driver-cups-pdf \
    && rm -rf /var/lib/apt/lists/*

# Configure CUPS for testing (allow all operations without auth)
RUN sed -i 's/Listen localhost:631/Listen 0.0.0.0:631/' /etc/cups/cupsd.conf && \
    sed -i 's/<Location \/>/<Location \/>\n  Allow All/' /etc/cups/cupsd.conf && \
    sed -i 's/<Location \/admin>/<Location \/admin>\n  Allow All/' /etc/cups/cupsd.conf && \
    sed -i 's/<Location \/admin\/conf>/<Location \/admin\/conf>\n  Allow All/' /etc/cups/cupsd.conf && \
    sed -i 's/LogLevel warn/LogLevel debug/' /etc/cups/cupsd.conf && \
    sed -i 's/Require user @SYSTEM/Allow All/g' /etc/cups/cupsd.conf && \
    sed -i 's/Require user @OWNER @SYSTEM/Allow All/g' /etc/cups/cupsd.conf && \
    echo "ServerAlias *" >> /etc/cups/cupsd.conf && \
    echo "DefaultEncryption Never" >> /etc/cups/cupsd.conf && \
    echo "DefaultAuthType None" >> /etc/cups/cupsd.conf && \
    echo "Browsing On" >> /etc/cups/cupsd.conf && \
    echo "BrowseLocalProtocols dnssd" >> /etc/cups/cupsd.conf && \
    echo "DefaultShared Yes" >> /etc/cups/cupsd.conf && \
    echo "<Policy default>" >> /etc/cups/cupsd.conf && \
    echo "  <Limit All>" >> /etc/cups/cupsd.conf && \
    echo "    Order allow,deny" >> /etc/cups/cupsd.conf && \
    echo "    Allow All" >> /etc/cups/cupsd.conf && \
    echo "  </Limit>" >> /etc/cups/cupsd.conf && \
    echo "</Policy>" >> /etc/cups/cupsd.conf

# Create output directory for PDF printer
RUN mkdir -p /var/spool/cups-pdf/ANONYMOUS && \
    chmod 777 /var/spool/cups-pdf/ANONYMOUS

# Add a virtual PDF printer
COPY cups-pdf.conf /etc/cups/cups-pdf.conf
COPY setup-printer.sh /setup-printer.sh
RUN chmod +x /setup-printer.sh

EXPOSE 631

CMD ["/setup-printer.sh"]

