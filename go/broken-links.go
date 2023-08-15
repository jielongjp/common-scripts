package main

import (
  "fmt"
  "net/http"
  "net/url"
  "regexp"
	"io"
	"strings"
)

func main() {
    var urlString string
    fmt.Print("URL: ")
    fmt.Scanln(&urlString)

    client := &http.Client{}

    req, err := http.NewRequest("GET", urlString, nil)
    if err != nil {
        fmt.Println(err)
        return
    }
    req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3")

    resp, err := client.Do(req)
    if err != nil {
        fmt.Println(err)
        return
    }
    defer resp.Body.Close()

    domain := getDomain(urlString)

    re := regexp.MustCompile(`href=["']?([^"'\s>]+)["']?`)
    bodyBytes, _ := io.ReadAll(resp.Body)
    bodyString := string(bodyBytes)
    matches := re.FindAllStringSubmatch(bodyString, -1)
    var links []string
    for _, match := range matches {
        link := match[1]
        if link != "" && link != "#" && !strings.HasPrefix(link, "javascript:void") {
            if strings.HasPrefix(link, "/") {
                link = domain + link
            } else if !strings.HasPrefix(link, "https://") {
                link = urlString + link
            }
            links = append(links, link)
        }
    }

    for _, link := range links {
        linkReq, err := http.NewRequest("GET", link, nil)
        if err != nil {
            fmt.Println(err)
            continue
        }
        linkReq.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3")

        linkResp, err := client.Do(linkReq)
        if err != nil {
            fmt.Println(err)
            continue
        }

        statusCode := linkResp.StatusCode
        color := "\033[0m"
        if statusCode >= 400 && statusCode < 500 {
            color = "\033[31m"
        } else if statusCode >= 500 {
            color = "\033[33m"
        }

        fmt.Printf("%s%s - %d\n", color, link, statusCode)

        linkResp.Body.Close()
    }
}

func getDomain(urlString string) string {
    parsed, err := url.Parse(urlString)
    if err != nil {
        return ""
    }
    return fmt.Sprintf("%s://%s", parsed.Scheme, parsed.Host)
}
