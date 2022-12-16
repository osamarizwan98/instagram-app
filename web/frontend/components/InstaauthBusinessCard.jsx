import { useEffect, useState } from "react";
import {
    Card,
    Heading,
    TextContainer,
    DisplayText,
    TextStyle,
    Layout,
    Page,
    Button,
} from "@shopify/polaris";
import { Toast } from "@shopify/app-bridge-react";
import { useAppQuery, useAuthenticatedFetch } from "../hooks";
import { useSearchParams } from "react-router-dom";

export function InstaauthBusinessCard() {
    const emptyToastProps = { content: null };
    const [isLoading, setIsLoading] = useState(false);
    const [toastProps, setToastProps] = useState(emptyToastProps);
    const [URL, setURL] = useState("");
    const [datas, setDatas] = useState([]);
    const fetch = useAuthenticatedFetch();
    const [searchParams] = useSearchParams();
    // const {
    //   data,
    //   refetch: refetchProductCount,
    //   isLoading: isLoadingCount,
    //   isRefetching: isRefetchingCount,
    // } = useAppQuery({
    //   url: "/api/loginInstaBusiness",
    //   reactQueryOptions: {
    //     onSuccess: () => {
    //       setIsLoading(false);
    //     },
    //   },
    // });

    useEffect(async () => {
        /** Check Status Already Register or not **/
        let hostName = window.location.hostname;

        const responseHost = await fetch("/api/status", {
            method: "POST",
            body: JSON.stringify({ shop: hostName }),
            headers: {
                "Content-type": "application/json", // We are sending JSON data
            },
        });

        let hostData = await responseHost.json();

        if (hostData.status != true) {
            /** Genrate Access Token **/
            let code = searchParams.get("code");
            if (code) {
                const response = await fetch("/api/instaToken", {
                    method: "POST",
                    body: JSON.stringify({ code: code, shop: hostName }),
                    headers: {
                        "Content-type": "application/json", // We are sending JSON data
                    },
                });
                let token_response = await response.json();
                setDatas(token_response);
                console.log(token_response);
            }
        } else {
            const responseInstaData = await fetch("/api/instaRawData", {
                method: "POST",
                body: JSON.stringify({ shop: hostName }),
                headers: {
                    "Content-type": "application/json", // We are sending JSON data
                },
            });
            let instaData = await responseInstaData.json();
            setDatas(instaData);
            // dataElement = instaData
            console.log(instaData);
        }

        // else{
        //  const responseApi = await fetch("/api/getData", {
        //     method: 'GET',
        //   })
        //   let data_response = await responseApi.json();
        //   console.log(data_response);
        // }
    }, [searchParams]);

    const toastMarkup = toastProps.content && (
        <Toast
            {...toastProps}
            onDismiss={() => setToastProps(emptyToastProps)}
        />
    );

    /** Genrate Auth Code of Instagram **/
    const connectInstagram = async () => {
        setIsLoading(true);
        const response = await fetch("/api/loginInstaBusiness");
        console.log(response);
        if (response.ok) {
            let instaResponse = await response.json();
            window.open(instaResponse.url, "_blank");
            // setURL(response.data.url)
            setIsLoading(false);
            // await refetchProductCount();
            // setToastProps({ content: "5 products created!" });
        } else {
            setIsLoading(false);
            setToastProps({
                content: "There was an error creating products",
                error: true,
            });
        }
    };

    return (
        <>
            {toastMarkup}
            <Card
                title="Product Counter"
                sectioned
                primaryFooterAction={{
                    content: "Connect With Instagram",
                    onAction: connectInstagram,
                    loading: isLoading,
                }}
            >
                <TextContainer spacing="loose">
                    <p>
                        Login with instagram to show Insta feed at site.
                        Business
                    </p>
                    {datas?.consumer ? (
                      <p>
                      Connected to @{datas?.consumer[0].username ? datas?.consumer[0].username : datas?.consumer.username } with Instagram 
                      <Button onClick={connectInstagram} plain> Change account </Button>
                      </p>
                    ) : null}
                </TextContainer>
            </Card>
            <Card
                title="Product Counter"
                sectioned
            >
            <Layout>
                {datas?.rawdata && datas?.rawdata?.length
                    ? datas?.rawdata?.map((element, index) => {
                          return (
                              <Layout.Section oneThird  key={index}>
                                      <img
                                          src={element.media_url}
                                          width="100%"
                                          height="100%"
                                          style={{
                                              objectFit: "cover",
                                              objectPosition: "center",
                                          }}
                                          alt="no image"
                                      />
                                      <TextContainer spacing="loose">{element.caption}</TextContainer>
                                 
                              </Layout.Section>
                          );
                      })
                    : null}
            </Layout>
            </Card>
        </>
    );
}
