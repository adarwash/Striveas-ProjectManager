#!/usr/bin/env python3
import os
import sys
import socket
import ssl
from imapclient import IMAPClient
from imapclient.exceptions import LoginError, IMAPClientError, ProtocolError

HOST = os.getenv("IMAP_HOST", "outlook.office365.com")
USERNAME = os.getenv("IMAP_USERNAME", "support@hiveitsupport.co.uk")
PASSWORD = os.getenv("IMAP_PASSWORD")  # set in your env, not in code
USE_SSL = True
TIMEOUT = 20  # seconds

def fail(msg, code=1):
    print(f"IMAP check: FAILED – {msg}")
    sys.exit(code)

def ok(msg):
    print(f"IMAP check: OK – {msg}")
    sys.exit(0)

def hint_auth():
    return (
        "Auth hint: Exchange Online has Basic Auth mostly disabled. "
        "Use OAuth2 (XOAUTH2) or ensure IMAP is enabled for the mailbox "
        "AND legacy auth is allowed (not recommended). If MFA is on, "
        "‘app passwords’ only work when legacy auth is enabled. "
        "Also verify: Admin Center → User → Mail → Mailbox features → IMAP: Enabled."
    )

def main():
    if not PASSWORD:
        fail("No password provided. Set IMAP_PASSWORD in the environment.", code=2)

    try:
        with IMAPClient(HOST, ssl=USE_SSL, timeout=TIMEOUT) as server:
            # Optional: increase to 4 for wire-level debugging
            server.debug = 0

            # Quick capability check (helps explain auth failures)
            caps = set()
            try:
                caps = set(server.capabilities())
            except Exception:
                pass

            try:
                server.login(USERNAME, PASSWORD)
            except LoginError as e:
                msg = str(e)
                if "AUTHENTICATIONFAILED" in msg.upper() or "535" in msg:
                    fail(f"Authentication failed for {USERNAME}. {hint_auth()}", code=10)
                fail(f"Login error: {msg}", code=10)

            # If we reach here, login worked
            acct = USERNAME
            details = []
            if "XOAUTH2" in {c.upper() for c in caps}:
                details.append("Server supports XOAUTH2")
            if caps:
                details.append("Capabilities: " + ", ".join(sorted(caps))[:200])

            ok(f"Logged in as {acct}. " + (" | ".join(details) if details else ""))

    except socket.timeout:
        fail(f"Network timeout after {TIMEOUT}s connecting to {HOST}. Check connectivity/DNS/firewall.", code=20)
    except socket.gaierror as e:
        fail(f"DNS/hostname error for {HOST}: {e}.", code=21)
    except ConnectionRefusedError:
        fail(f"Connection refused by {HOST}. Check firewall/port 993 and IMAP service status.", code=22)
    except ssl.SSLError as e:
        fail(f"TLS/SSL error: {e}. Check TLS version/certs and SSL interception.", code=23)
    except ProtocolError as e:
        fail(f"IMAP protocol error: {e}.", code=24)
    except IMAPClientError as e:
        fail(f"IMAP client error: {e}.", code=25)
    except Exception as e:
        fail(f"Unexpected error: {type(e).__name__}: {e}", code=99)

if __name__ == "__main__":
    main()
