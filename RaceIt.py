import httpx
import asyncio
from typing import Optional, List, Dict
from datetime import datetime
from urllib.parse import urlparse
import json
import statistics
import platform
import sys
from pathlib import Path
from rich.console import Console
from rich.table import Table
from rich.progress import Progress, SpinnerColumn, TextColumn
from rich.panel import Panel

class RaceConditionTester:
    def __init__(self):
        self.console = Console()
        self.response_times: List[float] = []
        self.cookies: Dict[str, str] = {}
        self.custom_headers: Dict[str, str] = {}
        self.output_dir = "race_condition_results"
        
        # Create output directory if it doesn't exist
        Path(self.output_dir).mkdir(exist_ok=True)

    def save_response_to_file(self, response: httpx.Response, index: int) -> str:
        """Save response content to a file and return the filename"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{self.output_dir}/response_{timestamp}_{index}.txt"
        
        with open(filename, 'w', encoding='utf-8') as f:
            # Write response metadata
            f.write(f"=== Response {index} ===\n")
            f.write(f"Status Code: {response.status_code}\n")
            f.write(f"Protocol: {response.http_version}\n")
            f.write("\n=== Headers ===\n")
            for key, value in response.headers.items():
                f.write(f"{key}: {value}\n")
            f.write("\n=== Content ===\n")
            f.write(response.text)
            
        return filename

    def add_header(self, header: str):
        """Add a custom header from string format 'key: value'"""
        try:
            key, value = header.split(':', 1)
            self.custom_headers[key.strip()] = value.strip()
        except ValueError:
            raise ValueError("Header must be in format 'key: value'")

    def get_base_headers(self) -> Dict[str, str]:
        """Get base headers with custom headers merged"""
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded', #TODO: Add support for JSON
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
            'Accept-Encoding': 'gzip, deflate, br',
            'Connection': 'keep-alive',
            'DNT': '1'
        }
        return {**headers, **self.custom_headers}

    async def make_request(
        self,
        client: httpx.AsyncClient,
        url: str,
        method: str,
        data: Optional[str] = None,
        json_data: Optional[Dict] = None
    ) -> httpx.Response:
        """Make a single HTTP request"""
        try:
            headers = self.get_base_headers()
            request_start = datetime.now()
            
            if method == "POST":
                if json_data:
                    headers['Content-Type'] = 'application/json'
                    response = await client.post(
                        url,
                        json=json_data,
                        headers=headers,
                        cookies=self.cookies,
                        follow_redirects=True
                    )
                else:
                    data_dict = {}
                    if isinstance(data, str) and data:
                        for item in data.split('&'):
                            if '=' in item:
                                key, value = item.split('=', 1)
                                data_dict[key] = value

                    response = await client.post(
                        url,
                        data=data_dict,
                        headers=headers,
                        cookies=self.cookies,
                        follow_redirects=True
                    )
            else:
                response = await client.get(
                    url,
                    headers=headers,
                    cookies=self.cookies,
                    follow_redirects=True
                )

            # Update cookies from response
            self.cookies.update(response.cookies)
            
            # Calculate response time
            request_time = (datetime.now() - request_start).total_seconds() * 1000
            self.response_times.append(request_time)
            
            return response

        except httpx.RequestError as e:
            self.console.print(f"[red]Request failed: {str(e)}[/red]")
            raise

    def create_response_table(self, response: httpx.Response, index: int, elapsed: float) -> Table:
        """Create a rich table for response details"""
        table = Table(title=f"Response {index}", show_header=True, header_style="bold magenta")
        table.add_column("Property", style="cyan")
        table.add_column("Value", style="green")

        table.add_row("Time", f"{elapsed:.2f}ms")
        table.add_row("Status", str(response.status_code))
        table.add_row("Protocol", response.http_version)
        
        # Add response content
        try:
            content = response.text
            if response.headers.get('content-type', '').startswith('application/json'):
                content = json.dumps(response.json(), indent=2)
        except Exception:
            content = response.text

        table.add_row("Content", content)
        return table

    def print_system_info(self):
        """Print system information"""
        system_info = Table(title="System Information", show_header=True, header_style="bold blue")
        system_info.add_column("Property", style="cyan")
        system_info.add_column("Value", style="green")

        system_info.add_row("Python Version", sys.version.split()[0])
        system_info.add_row("Platform", platform.platform())
        system_info.add_row("Machine", platform.machine())
        system_info.add_row("Processor", platform.processor() or "Unknown")

        self.console.print(system_info)

    async def process_responses(self, responses: List, start_time: datetime):
        """Process and display response information"""
        successful_requests = 0
        failed_requests = 0
        status_codes = {}
        total_time = (datetime.now() - start_time).total_seconds()
        saved_files = []

        # Create summary statistics
        if self.response_times:
            mean_time = statistics.mean(self.response_times)
            median_time = statistics.median(self.response_times)
            stdev_time = statistics.stdev(self.response_times) if len(self.response_times) > 1 else 0
            min_time = min(self.response_times)
            max_time = max(self.response_times)
        else:
            mean_time = median_time = stdev_time = min_time = max_time = 0
        
        # Process individual responses
        for i, response in enumerate(responses, 1):
            if isinstance(response, Exception):
                failed_requests += 1
                self.console.print(Panel(f"Request {i} failed: {str(response)}", style="red"))
            else:
                successful_requests += 1
                elapsed = self.response_times[i-1]
                status = response.status_code
                status_codes[status] = status_codes.get(status, 0) + 1

                # Save and display response
                filename = self.save_response_to_file(response, i)
                saved_files.append(filename)
                self.console.print(f"[green]Response {i} saved to: {filename}[/green]")

                # Create and display response table with content
                response_table = self.create_response_table(response, i, elapsed)
                self.console.print(response_table)

                # Print raw response content
                self.console.print(f"\n[cyan]Response {i} Content:[/cyan]")
                try:
                    if response.headers.get('content-type', '').startswith('application/json'):
                        self.console.print_json(response.text)
                    else:
                        self.console.print(response.text)
                except Exception as e:
                    self.console.print(f"[red]Error displaying content: {str(e)}[/red]")

        # Display summary tables and statistics as before...
       # Create and display summary table
        summary_table = Table(title="Test Summary", show_header=True, header_style="bold blue")
        summary_table.add_column("Metric", style="cyan")
        summary_table.add_column("Value", style="green")

        summary_table.add_row("Total Requests", str(len(responses)))
        summary_table.add_row("Successful Requests", str(successful_requests))
        summary_table.add_row("Failed Requests", str(failed_requests))
        summary_table.add_row("Success Rate", f"{(successful_requests/len(responses))*100:.1f}%")
        summary_table.add_row("Total Time", f"{total_time:.2f} seconds")
        summary_table.add_row("Average Time per Request", f"{(total_time/len(responses))*1000:.2f}ms")
        summary_table.add_row("Mean Response Time", f"{mean_time:.2f}ms")
        summary_table.add_row("Median Response Time", f"{median_time:.2f}ms")
        summary_table.add_row("Standard Deviation", f"{stdev_time:.2f}ms")
        summary_table.add_row("Minimum Response Time", f"{min_time:.2f}ms")
        summary_table.add_row("Maximum Response Time", f"{max_time:.2f}ms")

        self.console.print(summary_table)

        # Display status code distribution
        status_table = Table(title="Status Code Distribution", show_header=True, header_style="bold green")
        status_table.add_column("Status Code", style="cyan")
        status_table.add_column("Count", style="green")
        status_table.add_column("Percentage", style="yellow")

        for status, count in sorted(status_codes.items()):
            percentage = (count/len(responses))*100
            status_table.add_row(
                str(status),
                str(count),
                f"{percentage:.1f}%"
            )

        self.console.print(status_table)

        # Display summary of saved files
        self.console.print("\n[bold cyan]Response files have been saved to the following location:[/bold cyan]")
        for file in saved_files:
            self.console.print(f"[green]- {file}[/green]")

    async def execute_race(
        self,
        url: str,
        method: str,
        data: str,
        num_requests: int,
        json_mode: bool = False
    ):
        """Execute the race condition test"""
        try:
            # Validate URL
            parsed_url = urlparse(url)
            if not all([parsed_url.scheme, parsed_url.netloc]):
                raise ValueError("Invalid URL format")

            client_config = {
                'timeout': httpx.Timeout(30.0),
                'limits': httpx.Limits(max_keepalive_connections=num_requests, max_connections=num_requests),
                'transport': httpx.AsyncHTTPTransport(retries=0)
            }

            self.print_system_info()

            config_table = Table(title="Test Configuration", show_header=True, header_style="bold blue")
            config_table.add_column("Parameter", style="cyan")
            config_table.add_column("Value", style="green")

            config_table.add_row("Target URL", url)
            config_table.add_row("Method", method)
            config_table.add_row("Data", data if not json_mode else "JSON data")
            config_table.add_row("Number of Requests", str(num_requests))
            config_table.add_row("Content Type", "application/json" if json_mode else "application/x-www-form-urlencoded")

            self.console.print(config_table)

            json_data = None
            if json_mode and data:
                try:
                    json_data = json.loads(data)
                except json.JSONDecodeError:
                    raise ValueError("Invalid JSON data provided")

            with Progress(
                SpinnerColumn(),
                TextColumn("[progress.description]{task.description}"),
                transient=True,
            ) as progress:
                progress.add_task(description="Preparing requests...", total=None)
                
                async with httpx.AsyncClient(**client_config) as client:
                    requests = [
                        self.make_request(client, url, method, data, json_data)
                        for _ in range(num_requests)
                    ]

                    await asyncio.sleep(0.1)
                    self.console.print("[yellow]Launching requests simultaneously...[/yellow]")
                    start_time = datetime.now()

                    responses = await asyncio.gather(*requests, return_exceptions=True)
                    await self.process_responses(responses, start_time)

        except Exception as e:
            self.console.print(f"[red]Error during race condition test: {str(e)}[/red]")

async def main():
    """Main execution function"""
    try:
        tester = RaceConditionTester()
        console = Console()

        url = console.input("[cyan]Enter the URL (e.g., https://example.com/cart): [/cyan]").strip()
        if not url.startswith(('http://', 'https://')):
            raise ValueError("URL must start with http:// or https://")

        method = console.input("[cyan]Enter HTTP method (GET/POST) [default: GET]: [/cyan]").strip().upper() or "GET"
        if method not in ["GET", "POST"]:
            raise ValueError("Method must be either GET or POST")

        data = ""
        json_mode = False
        if method == "POST":
            content_type = console.input("[cyan]Content type (json/form) [default: form]: [/cyan]").strip().lower() or "form"
            json_mode = content_type == "json"
            
            if json_mode:
                data = console.input("[cyan]Enter JSON data: [/cyan]").strip()
            else:
                data = console.input("[cyan]Enter POST data (key1=value1&key2=value2): [/cyan]").strip()

        num_requests = int(console.input("[cyan]Enter the number of requests [default: 10]: [/cyan]") or "10")
        if num_requests <= 0:
            raise ValueError("Number of requests must be positive")

        # Optional custom headers
        add_headers = console.input("[cyan]Add custom headers? (y/n) [default: n]: [/cyan]").strip().lower() or "n"
        if add_headers == "y":
            while True:
                header = console.input("[cyan]Enter header (key: value) or press enter to continue: [/cyan]").strip()
                if not header:
                    break
                tester.add_header(header)

        await tester.execute_race(url, method, data, num_requests, json_mode)

    except ValueError as ve:
        console.print(f"[red]Input error: {str(ve)}[/red]")
    except Exception as e:
        console.print(f"[red]Unexpected error: {str(e)}[/red]")
        raise

if __name__ == "__main__":
    asyncio.run(main())