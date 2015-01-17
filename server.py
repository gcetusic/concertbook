import sys
import urlparse
import BaseHTTPServer
from SimpleHTTPServer import SimpleHTTPRequestHandler

from tags import main


class ConcertBookHandler(SimpleHTTPRequestHandler):
    def do_GET(self):
        # parse query data & params to find out what was passed
        parsed_params = urlparse.urlparse(self.path)
        query_parsed = urlparse.parse_qs(parsed_params.query)

        # request is either for a file to be served up or our test
        if parsed_params.path == "/tags":
            artists = query_parsed['artists'][0].split(',')
            tags = main(artists)
            self.process_request(tags)
        else:
            # default to serve up a local file
            SimpleHTTPRequestHandler.do_GET(self)

    def process_request(self, result):

        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()

        self.wfile.write(result)
        self.wfile.close()


HandlerClass = ConcertBookHandler
ServerClass = BaseHTTPServer.HTTPServer
Protocol = "HTTP/1.0"

if sys.argv[1:]:
    port = int(sys.argv[1])
else:
    port = 8000
server_address = ('0.0.0.0', port)

HandlerClass.protocol_version = Protocol
httpd = ServerClass(server_address, HandlerClass)

sa = httpd.socket.getsockname()
print "Serving HTTP on", sa[0], "port", sa[1], "..."
httpd.serve_forever()
