import sys
import urlparse
from BaseHTTPServer import HTTPServer, BaseHTTPRequestHandler
from SocketServer import ThreadingMixIn

from tags import main
from artists import get_artists


class ConcertBookHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        # parse query data & params to find out what was passed
        parsed_params = urlparse.urlparse(self.path)
        query_parsed = urlparse.parse_qs(parsed_params.query)

        # request is either for a file to be served up or our test
        if parsed_params.path == "/tags":
            artists = query_parsed['artists'][0].split(',')
            tags = main(artists)
            self.process_request(tags)
        elif parsed_params.path == "/artists":
            artists = query_parsed['artists'][0].split(',')
            tags = get_artists(artists)
            self.process_request(tags)
        else:
            # default to serve up a local file
            BaseHTTPRequestHandler.do_GET(self)

    def process_request(self, result):
        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()

        self.wfile.write(result)
        self.wfile.close()


class ThreadedHTTPServer(ThreadingMixIn, HTTPServer):
    daemon_threads = True

Handler = ConcertBookHandler
Server = ThreadedHTTPServer
Protocol = "HTTP/1.1"

if sys.argv[1:]:
    port = int(sys.argv[1])
else:
    port = 8000
server_address = ('0.0.0.0', port)

Handler.protocol_version = Protocol
httpd = Server(server_address, Handler)

if __name__ == '__main__':
    sa = httpd.socket.getsockname()
    print 'Starting server, use <Ctrl-C> to stop'
    print "Serving HTTP on", sa[0], "port", sa[1], "..."
    httpd.serve_forever()
